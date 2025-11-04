async function getWeather() {
  const city = document.getElementById("city").value.trim();
  const token = document.getElementById("token").value.trim();
  const card = document.getElementById("weather-card");
  const error = document.getElementById("error");
  const bg = document.getElementById("bg-animation");

  if (!city || !token) {
    error.textContent = "لطفاً نام شهر و توکن را وارد کنید.";
    return;
  }

  error.textContent = "در حال دریافت اطلاعات...";
  card.classList.remove("show");

  try {
    const res = await fetch(`weather.php?city=${encodeURIComponent(city)}&token=${encodeURIComponent(token)}`);
    const json = await res.json();

    if (json.error) {
      error.textContent = "❌ " + json.error;
      return;
    }

    const data = json.data;
    error.textContent = "";

    // دما از Kelvin به Celsius
    const temp = (data.main.temp - 273.15).toFixed(1);
    const feels = (data.main.feels_like - 273.15).toFixed(1);
    const desc = data.weather[0].description;
    const icon = data.weather[0].icon;
    const sunrise = new Date(data.sys.sunrise * 1000).toLocaleTimeString("fa-IR");
    const sunset = new Date(data.sys.sunset * 1000).toLocaleTimeString("fa-IR");

    document.getElementById("city-name").textContent = data.name;
    document.getElementById("country").textContent = `کشور: ${data.sys.country}`;
    document.getElementById("temperature").textContent = `${temp}°C (احساس: ${feels}°C)`;
    document.getElementById("description").textContent = desc;
    document.getElementById("weather-icon").src = `https://openweathermap.org/img/wn/${icon}@4x.png`;
    document.getElementById("humidity").textContent = data.main.humidity;
    document.getElementById("wind").textContent = data.wind.speed;
    document.getElementById("pressure").textContent = data.main.pressure;
    document.getElementById("visibility").textContent = data.visibility;
    document.getElementById("sunrise").textContent = sunrise;
    document.getElementById("sunset").textContent = sunset;

    // ✨ نمایش کارت با انیمیشن
    setTimeout(() => card.classList.add("show"), 100);

    // 🌈 پس‌زمینه داینامیک
    const mainWeather = data.weather[0].main.toLowerCase();
    createAnimatedBackground(mainWeather, bg);

  } catch (e) {
    error.textContent = "⚠️ خطا در ارتباط با سرور.";
  }
}

function createAnimatedBackground(weather, container) {
  container.innerHTML = ""; // پاک کردن قبلی
  if (weather === "clouds") {
    for (let i = 0; i < 5; i++) {
      const cloud = document.createElement("div");
      cloud.classList.add("cloud");
      cloud.style.width = `${100 + Math.random() * 100}px`;
      cloud.style.height = `${60 + Math.random() * 40}px`;
      cloud.style.top = `${Math.random() * 60}%`;
      cloud.style.left = `${Math.random() * 100}%`;
      cloud.style.animationDuration = `${40 + Math.random() * 40}s`;
      container.appendChild(cloud);
    }
  } else if (weather === "rain") {
    for (let i = 0; i < 80; i++) {
      const drop = document.createElement("div");
      drop.classList.add("rain-drop");
      drop.style.left = `${Math.random() * 100}%`;
      drop.style.animationDuration = `${0.5 + Math.random()}s`;
      drop.style.animationDelay = `${Math.random()}s`;
      container.appendChild(drop);
    }
  } else if (weather === "clear") {
    container.style.background = "radial-gradient(circle at 50% 20%, #ffecb3, transparent)";
  } else {
    container.style.background = "";
  }
}
