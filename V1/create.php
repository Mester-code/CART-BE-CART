<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// بررسی API Key
checkApiKey();

// دریافت پارامترها
$chatId = $_REQUEST['chat_id'] ?? '';
$amount = $_REQUEST['amount'] ?? 0;
$type = $_REQUEST['type'] ?? 'rial'; // tmn یا rial
$name = $_REQUEST['name'] ?? '';

// اعتبارسنجی
if (empty($chatId) || empty($amount) || empty($name)) {
    echo json_encode([
        'success' => false,
        'error' => 'پارامترهای chat_id، amount و name الزامی هستند'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// تبدیل مبلغ به ریال
$amount = (int)$amount;
if ($type === 'tmn') {
    $amountRial = $amount * 10;
} else {
    $amountRial = $amount;
}

// خواندن دیتابیس
$data = readJsonFile(DATA_FILE);

// مقداردهی اولیه اگر فایل خالی است
if (empty($data)) {
    $data = [
        'current_card_index' => 0,
        'last_tracking_code' => 0,
        'transactions' => []
    ];
}

// انتخاب کارت و تولید شناسه
$cards = json_decode(CARDS, true);
$currentCardIndex = $data['current_card_index'] ?? 0;
$lastTrackingCode = $data['last_tracking_code'] ?? 0;

// اگر شناسه به ۹۹۹۹ رسید، برو به کارت بعدی
if ($lastTrackingCode >= MAX_TRACKING_CODE) {
    $currentCardIndex++;
    $lastTrackingCode = 0;
    
    // اگر همه کارت‌ها پر شدن، خطا بده
    if ($currentCardIndex >= count($cards)) {
        echo json_encode([
            'success' => false,
            'error' => 'ظرفیت تمام کارت‌ها برای امروز پر شده است. لطفاً فردا مجدداً تلاش کنید.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// تولید شناسه جدید
$lastTrackingCode++;
$finalAmountRial = $amountRial + $lastTrackingCode;

// محاسبه زمان انقضا (۶۰ دقیقه)
$now = time();
$expiresAt = $now + (TRANSACTION_VALIDITY_MINUTES * 60);

// ساخت تراکنش
$newTx = [
    'id' => 'TX_' . time() . '_' . rand(1000, 9999),
    'chat_id' => $chatId,
    'name' => $name,
    'base_amount_rial' => $amountRial,
    'tracking_code' => $lastTrackingCode,
    'final_amount_rial' => $finalAmountRial,
    'status' => 'pending',
    'created_at' => date('Y-m-d H:i:s', $now),
    'expires_at' => date('Y-m-d H:i:s', $expiresAt),
    'card_index' => $currentCardIndex
];

// ذخیره در دیتابیس
$data['transactions'][] = $newTx;
$data['current_card_index'] = $currentCardIndex;
$data['last_tracking_code'] = $lastTrackingCode;

writeJsonFile(DATA_FILE, $data);

// ==========================================
// 📞 Callback - برگرداندن اطلاعات به کاربر
// ==========================================
$selectedCard = $cards[$currentCardIndex];

echo json_encode([
    'success' => true,
    'message' => 'تراکنش با موفقیت ایجاد شد',
    'callback' => [
        'card_number' => $selectedCard['number'],
        'card_bank' => $selectedCard['bank'],
        'card_owner' => $selectedCard['owner'],
        'amount_to_pay_rial' => $finalAmountRial,
        'amount_to_pay_toman' => $finalAmountRial / 10,
        'tracking_code' => $lastTrackingCode,
        'expires_at' => date('Y-m-d H:i:s', $expiresAt),
        'expires_in_minutes' => TRANSACTION_VALIDITY_MINUTES
    ],
    'instruction' => "لطفاً دقیقاً مبلغ " . number_format($finalAmountRial) . " ریال را به شماره کارت {$selectedCard['number']} واریز کنید. این تراکنش تا " . TRANSACTION_VALIDITY_MINUTES . " دقیقه اعتبار دارد."
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
