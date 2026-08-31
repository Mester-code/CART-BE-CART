<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';


checkApiKey();


$dataDb = readJsonFile(DATA_FILE);
$archiveDb = readJsonFile(ARCHIVE_FILE);

if (empty($dataDb)) {
    echo json_encode([
        'success' => true,
        'message' => 'دیتابیس خالی است',
        'archived_count' => 0
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


if (empty($archiveDb)) {
    $archiveDb = ['transactions' => []];
}

$now = time();
$today = date('Y-m-d');
$archivedCount = 0;
$activeTransactions = [];

foreach ($dataDb['transactions'] as $tx) {
    $txDate = date('Y-m-d', strtotime($tx['created_at']));

  
    if ($txDate !== $today || $tx['status'] === 'expired') {
        $archiveDb['transactions'][] = $tx;
        $archivedCount++;
    } else {
        $activeTransactions[] = $tx;
    }
}

// آپدیت دیتابیس اصلی
$dataDb['transactions'] = $activeTransactions;


if (empty($activeTransactions)) {
    $dataDb['last_tracking_code'] = 0;
    $dataDb['current_card_index'] = 0;
}

writeJsonFile(DATA_FILE, $dataDb);
writeJsonFile(ARCHIVE_FILE, $archiveDb);

echo json_encode([
    'success' => true,
    'message' => 'عملیات آرشیو با موفقیت انجام شد',
    'archived_count' => $archivedCount,
    'remaining_active' => count($activeTransactions),
    'reset_tracking_codes' => empty($activeTransactions)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
