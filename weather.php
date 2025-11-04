<?php
header('Content-Type: application/json; charset=utf-8');
require 'config.php';

// بررسی ورودی‌ها
$city  = isset($_GET['city']) ? trim($_GET['city']) : null;
$token = isset($_GET['token']) ? trim($_GET['token']) : null;

// مدیریت خطا برای ورودی‌ها
if (!$city) {
    http_response_code(400);
    echo json_encode(['error' => 'نام شهر الزامی است.']);
    exit;
}

if ($token !== VALID_TOKEN) {
    http_response_code(401);
    echo json_encode(['error' => 'توکن معتبر نیست.']);
    exit;
}

// چک کردن cache
$cacheFile = 'cache.json';
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    if ($cacheData && strtolower($cacheData['city']) === strtolower($city)) {
        echo json_encode([
            'source' => 'cache',
            'data' => $cacheData['data']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// اگر در cache نبود → درخواست جدید به API
$url = OPENWEATHER_URL . "?q=" . urlencode($city) . "&appid=" . OPENWEATHER_API_KEY;

// درخواست با CURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// بررسی خطا
if ($httpCode != 200 || !$response) {
    // اگر قبلاً cache داشتیم، همان را نشان می‌دهیم
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        echo json_encode([
            'warning' => 'عدم ارتباط با سرور. داده‌ی cache نمایش داده می‌شود.',
            'data' => $cacheData['data']
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code($httpCode);
        echo json_encode(['error' => 'خطا در دریافت اطلاعات از سرور.']);
    }
    exit;
}

// تبدیل پاسخ به JSON
$data = json_decode($response, true);

// ذخیره در cache
file_put_contents($cacheFile, json_encode([
    'city' => $city,
    'data' => $data
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// نمایش نتیجه
echo json_encode([
    'source' => 'api',
    'data' => $data
], JSON_UNESCAPED_UNICODE);
?>
