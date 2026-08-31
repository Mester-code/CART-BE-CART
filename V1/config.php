<?php
/**
 * فایل تنظیمات مرکزی سیستم پرداخت
 * تمام تنظیمات مهم در اینجا قرار دارند
 */

// ==========================================
// 🔐 امنیت - API Key
// ==========================================
define('API_KEY', 'your key');

// ==========================================
// 💳 اطلاعات کارت‌های بانکی
// کار برای گرامی در نسخه شماره ۱ تنها از بلوبانک می‌توان استفاده کرد شما می‌توانید دو حساب بلوبانک یعنی حساب اصلی خود و حساب کسب و کار خود را اضافه کنید
// ==========================================
define('CARDS', json_encode([
    [
        'number' => '6219-8610-xxxx-xxxx',
        'bank' => 'بلو بانک',
        'owner' => 'محمدمهدی '
    ]
]));

// ==========================================
// ⏰ زمان اعتبار تراکنش
// ==========================================
define('TRANSACTION_VALIDITY_MINUTES', 60); // ۶۰ دقیقه

// ==========================================
// 🔢 محدوده شناسه‌ها
// ==========================================
define('MIN_TRACKING_CODE', 1);
define('MAX_TRACKING_CODE', 9999);

// ==========================================
// 📁 مسیر فایل‌ها
// ==========================================
define('DATA_FILE', __DIR__ . '/data.json');
define('ARCHIVE_FILE', __DIR__ . '/archive.json');
define('MESSAGES_FILE', __DIR__ . '/messages.json');

// ==========================================
// 🕐 تنظیمات زمان
// ==========================================
date_default_timezone_set('Asia/Tehran');

// ==========================================
// 🔧 توابع کمکی
// ==========================================

/**
 * بررسی API Key
 */
function checkApiKey() {
    $providedKey = $_REQUEST['api_key'] ?? '';
    if ($providedKey !== API_KEY) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'دسترسی غیرمجاز - API Key نامعتبر است'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * خواندن امن فایل JSON با قفل
 */
function readJsonFile($filepath) {
    if (!file_exists($filepath)) {
        return [];
    }
    $fp = fopen($filepath, 'r');
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return json_decode($content, true) ?: [];
}

/**
 * نوشتن امن فایل JSON با قفل
 */
function writeJsonFile($filepath, $data) {
    $fp = fopen($filepath, 'c+');
    flock($fp, LOCK_EX);
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}
