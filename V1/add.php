<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';


checkApiKey();


$sms = $_REQUEST['sms_body'] ?? '';

if (empty($sms)) {
    echo json_encode([
        'success' => false,
        'error' => 'پارامتر sms_body الزامی است'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


if (strpos($sms, 'واریز پول') === false && strpos($sms, 'واریز') === false) {
    echo json_encode([
        'success' => false,
        'error' => 'این پیامک واریز نیست'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


$sms = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $sms);
$sms = str_replace(['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'], ['0','1','2','3','4','5','6','7','8','9'], $sms);


$amountRial = 0;
if (preg_match('/([\d,]+)\s*ریال\s*به\s*حساب\s*شما\s*نشست/u', $sms, $m)) {
    $amountRial = (int) str_replace(',', '', $m[1]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'مبلغ در پیامک یافت نشد'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


$time = '';
preg_match('/(\d{1,2}:\d{2})/', $sms, $m) ? $time = $m[1] : $time = 'unknown';

$date = '';
preg_match('/(\d{4}\.\d{2}\.\d{2})/', $sms, $m) ? $date = $m[1] : $date = 'unknown';


$messages = readJsonFile(MESSAGES_FILE);
if (empty($messages)) {
    $messages = ['messages' => []];
}


$newMsg = [
    'msg_id' => 'MSG_' . time() . '_' . rand(100, 999),
    'raw_body' => $sms,
    'amount_rial' => $amountRial,
    'time' => $time,
    'date' => $date,
    'is_deposit' => true,
    'verify' => 0,
    'received_at' => date('Y-m-d H:i:s')
];

$messages['messages'][] = $newMsg;

writeJsonFile(MESSAGES_FILE, $messages);

echo json_encode([
    'success' => true,
    'message' => 'پیامک با موفقیت ثبت شد',
    'data' => $newMsg
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
