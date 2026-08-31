<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// بررسی API Key
checkApiKey();

$hangtId = trim($_REQUEST['hangt_id'] ?? '');

if (empty($hangtId)) {
    echo json_encode([
        'success' => false,
        'error' => 'پارامتر hangt_id الزامی است'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// خواندن دیتابیس‌ها
$dataDb = readJsonFile(DATA_FILE);
$msgDb = readJsonFile(MESSAGES_FILE);

if (empty($dataDb)) {
    echo json_encode([
        'success' => false,
        'error' => 'دیتابیس خالی است'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$now = time();
$targetTx = null;

// پیدا کردن تراکنش
foreach ($dataDb['transactions'] as $index => $tx) {
    if (trim($tx['id'] ?? '') === $hangtId) {
        $targetTx = &$dataDb['transactions'][$index];
        break;
    }
}

if (!$targetTx) {
    echo json_encode([
        'success' => false,
        'error' => 'تراکنشی با این hangt_id یافت نشد',
        'debug_info' => [
            'شما_این_مقدار_را_فرستادید' => $hangtId,
            'آی‌دی‌های_موجود' => array_column($dataDb['transactions'], 'id')
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// بررسی انقضا
$expTime = strtotime($targetTx['expires_at']);

if ($targetTx['status'] === 'pending' && $now > $expTime) {
    $targetTx['status'] = 'expired';
    writeJsonFile(DATA_FILE, $dataDb);
    
    echo json_encode([
        'success' => true,
        'status' => 'expired',
        'message' => 'زمان این تراکنش (۶۰ دقیقه) به پایان رسیده و منقضی شده است.',
        'data' => $targetTx
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// بررسی فعال
if ($targetTx['status'] === 'pending' && !empty($msgDb)) {
    $matchFound = false;
    
    foreach ($msgDb['messages'] as &$msg) {
        if (($msg['is_deposit'] ?? true) == true && ($msg['verify'] ?? 0) == 0) {
            if ((int)$msg['amount_rial'] === (int)$targetTx['final_amount_rial']) {
                $targetTx['status'] = 'paid';
                $targetTx['paid_at'] = date('Y-m-d H:i:s');
                $targetTx['matched_msg_id'] = $msg['msg_id'];
                
                $msg['verify'] = 1;
                $msg['matched_hangt_id'] = $hangtId;
                
                $matchFound = true;
                break;
            }
        }
    }
    
    if ($matchFound) {
        writeJsonFile(DATA_FILE, $dataDb);
        writeJsonFile(MESSAGES_FILE, $msgDb);
        
        echo json_encode([
            'success' => true,
            'status' => 'paid',
            'message' => '✅ پرداخت با موفقیت تایید و ثبت شد.',
            'data' => $targetTx
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

$statusMessage = ($targetTx['status'] === 'paid') 
    ? 'این تراکنش قبلاً با موفقیت پرداخت شده است.' 
    : 'تراکنش در انتظار پرداخت است.';

echo json_encode([
    'success' => true,
    'status' => $targetTx['status'],
    'message' => $statusMessage,
    'data' => $targetTx
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
