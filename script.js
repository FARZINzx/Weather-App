async function getWeather() {
  const city = document.getElementById('city').value.trim();
  const token = document.getElementById('token').value.trim();
  const resultDiv = document.getElementById('result');

  if (!city || !token) {
    resultDiv.innerHTML = "<p style='color:red;'>لطفاً نام شهر و توکن را وارد کنید.</p>";
    return;
  }

  resultDiv.innerHTML = "⏳ در حال دریافت اطلاعات...";

  try {
    const response = await fetch(`weather.php?city=${encodeURIComponent(city)}&token=${encodeURIComponent(token)}`);
    const data = await response.json();

    if (data.error) {
      resultDiv.innerHTML = `<p style='color:red;'>❌ ${data.error}</p>`;
      return;
    }

    if (data.warning) {
      resultDiv.innerHTML = `<p style='color:orange;'>⚠️ ${data.warning}</p>`;
    }

    const weather = data.data;
    const temp = weather.main.temp;
    const desc = weather.weather[0].description;
    const icon = weather.weather[0].icon;

    resultDiv.innerHTML = `
      <h3>${weather.name}</h3>
      <img class="weather-icon" src="https://openweathermap.org/img/wn/${icon}@2x.png" alt="weather icon">
      <p>دمای فعلی: ${temp}°C</p>
      <p>وضعیت: ${desc}</p>
      <small>منبع: ${data.source === "cache" ? "حافظه (Cache)" : "API"}</small>
    `;

  } catch (error) {
    resultDiv.innerHTML = `<p style='color:red;'>خطا در ارتباط با سرور.</p>`;
  }
}
