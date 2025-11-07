<?php
require 'config.php';

// Initialize variables
$weather_data = null;
$error = '';
$city_input = '';
$source = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city_input = trim($_POST['city'] ?? '');

    if (!$city_input) {
        $error = '⚠️ لطفاً نام شهر را وارد کنید.';
    } else {
        $cacheFile = 'cache.json';
        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if ($cacheData && strtolower($cacheData['city']) === strtolower($city_input)) {
                $weather_data = $cacheData['data'];
                $source = 'cache';
            }
        }

        if (!$weather_data) {
            $url = OPENWEATHER_URL . "?q=" . urlencode($city_input) . "&appid=" . OPENWEATHER_API_KEY;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200 && $response) {
                $weather_data = json_decode($response, true);
                $source = 'API';
                file_put_contents($cacheFile, json_encode([
                    'city' => $city_input,
                    'data' => $weather_data
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            } else {
                switch ($httpCode) {
                    case 404:
                        $error = '❌ شهر وارد شده پیدا نشد.';
                        break;
                    case 401:
                        $error = '🔑 کلید API معتبر نیست.';
                        break;
                    default:
                        $error = '🌐 خطا در برقراری ارتباط با سرور.';
                        break;
                }
            }
        }
    }
}

function kelvinToCelsius($kelvin)
{
    return round($kelvin - 273.15, 1);
}

function formatTime($timestamp)
{
    return date('H:i', $timestamp);
}

function getWeatherClass($weather_main)
{
    $weather_main = strtolower($weather_main);
    return match ($weather_main) {
        'clear' => 'weather-clear',
        'clouds' => 'weather-clouds',
        'rain' => 'weather-rain',
        'snow' => 'weather-snow',
        default => 'weather-default'
    };
}

$weather_class = $weather_data ? getWeatherClass($weather_data['weather'][0]['main']) : 'weather-default';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>آب‌وهوا | OpenWeather</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $weather_class; ?>">
    <div class="container">
        <?php if ($weather_data && $source): ?>
            <div class="source">منبع: <?php echo htmlspecialchars($source); ?></div>
        <?php endif; ?>

        <form method="POST" class="search-form">
            <input type="text" name="city" placeholder="نام شهر..." value="<?php echo htmlspecialchars($city_input); ?>"
                class="form-input">
            <button type="submit" class="submit-btn">جستجو</button>
        </form>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($weather_data): ?>
            <div class="weather-card">
                <div class="city-info">
                    <h1 class="city-name"><?php echo htmlspecialchars($weather_data['name']); ?></h1>
                    <p class="country">کشور: <?php echo htmlspecialchars($weather_data['sys']['country']); ?></p>
                </div>

                <img src="https://openweathermap.org/img/wn/<?php echo $weather_data['weather'][0]['icon']; ?>@4x.png"
                    alt="<?php echo $weather_data['weather'][0]['description']; ?>" class="weather-icon">

                <h2 class="temperature">
                    <?php echo kelvinToCelsius($weather_data['main']['temp']); ?>°C
                </h2>
                <p class="description"><?php echo htmlspecialchars($weather_data['weather'][0]['description']); ?></p>

                <div class="weather-details">
                    <div class="detail-item">💧 رطوبت: <?php echo $weather_data['main']['humidity']; ?>%</div>
                    <div class="detail-item">🌬️ باد: <?php echo $weather_data['wind']['speed']; ?> m/s</div>
                    <div class="detail-item">🌡️ فشار: <?php echo $weather_data['main']['pressure']; ?> hPa</div>
                    <div class="detail-item">👁 دید: <?php echo $weather_data['visibility']; ?> m</div>
                    <div class="detail-item">🌅 طلوع: <?php echo formatTime($weather_data['sys']['sunrise']); ?></div>
                    <div class="detail-item">🌇 غروب: <?php echo formatTime($weather_data['sys']['sunset']); ?></div>
                </div>
            </div>
        <?php endif; ?>
        <a class="github-info" href="https://github.com/FARZINzx/Weather-App" target="_blank">
            <img width="30px" height="30px" src="./assets/images/github.svg" alt="git gub icon" />
        </a>
    </div>
</body>

</html>