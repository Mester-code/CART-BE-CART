<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// خواندن دیتابیس‌ها
$dataDb = readJsonFile(DATA_FILE);
$msgDb = readJsonFile(MESSAGES_FILE);

if (empty($dataDb) || empty($msgDb)) {
    echo json_encode([
        'success' => true,
        'message' => 'دیتابیس خالی است',
        'details' => []
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$now = time();
$matchedCount = 0;
$details = [];

foreach ($msgDb['messages'] as &$msg) {
    if (($msg['verify'] ?? 0) == 0 && ($msg['is_deposit'] ?? true) == true) {
        $msgAmount = (int)$msg['amount_rial'];
        
        foreach ($dataDb['transactions'] as &$tx) {
            $expTime = strtotime($tx['expires_at']);
            
            if ($tx['status'] == 'pending' && $expTime > $now) {
                if ($tx['final_amount_rial'] == $msgAmount) {
                    
                    // ✅ تطابق پیدا شد
                    $tx['status'] = 'paid';
                    $tx['paid_at'] = date('Y-m-d H:i:s');
                    $tx['matched_msg_id'] = $msg['msg_id'];
                    
                    $msg['verify'] = 1;
                    $msg['matched_hangt_id'] = $tx['id'];
                    
                    $matchedCount++;
                    $details[] = "تراکنش {$tx['id']} کاربر {$tx['name']} (چت آیدی: {$tx['chat_id']}) با مبلغ {$msgAmount} ریال تایید شد.";
                    
                    // ==========================================
                    // 🔔 محل توسعه آینده (Future Hooks)
                    // ==========================================
                    // ۱. ارسال نوتیفیکیشن به ربات تلگرام کاربر
                    // sendTelegramNotification($tx['chat_id'], "پرداخت شما تایید شد");
                    
                    // ۲. ارسال پیامک به ادمین
                    // sendAdminSms("واریز جدید: {$tx['name']} - {$msgAmount} ریال");
                    
                    // ۳. آپدیت موجودی کاربر در دیتابیس SQL
                    // $pdo->query("UPDATE users SET balance = balance + {$tx['base_amount_rial']} WHERE chat_id = '{$tx['chat_id']}'");
                    // ==========================================
                    
                    break;
                }
            } else if ($tx['status'] == 'pending' && $expTime <= $now) {
                $tx['status'] = 'expired';
            }
        }
    }
}

// ذخیره تغییرات
writeJsonFile(DATA_FILE, $dataDb);
writeJsonFile(MESSAGES_FILE, $msgDb);

if ($matchedCount > 0) {
    echo json_encode([
        'success' => true,
        'message' => "{$matchedCount} تراکنش با موفقیت مچ و تایید شد.",
        'details' => $details
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'success' => true,
        'message' => 'هیچ تطابق جدیدی یافت نشد.',
        'details' => []
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
