
document.addEventListener('DOMContentLoaded', function () {
  const loginForm = document.getElementById('loginForm');
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');
  const captchaInput = document.getElementById('captcha');
  const errorMessage = document.getElementById('errorMessage');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      const username = usernameInput ? usernameInput.value.trim() : '';
      const password = passwordInput ? passwordInput.value.trim() : '';
      const captcha = captchaInput ? captchaInput.value.trim() : '';
      if (!username) {
        showError('请输入用户名');
        if (usernameInput) usernameInput.focus();
        e.preventDefault();
        return false;
      }

      if (!password) {
        showError('请输入密码');
        if (passwordInput) passwordInput.focus();
        e.preventDefault();
        return false;
      }
      if (captchaInput && !captcha) {
        showError('请输入验证码');
        captchaInput.focus();
        e.preventDefault();
        return false;
      }
      const submitBtn = loginForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '登录中...';
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '登 录';
        }, 3000);
      }
    });
  }
  if (usernameInput) {
    usernameInput.focus();
  }
  document.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      const activeElement = document.activeElement;
      if (activeElement && (
        activeElement.id === 'username' ||
        activeElement.id === 'password' ||
        activeElement.id === 'captcha'
      )) {
        const form = activeElement.closest('form');
        if (form) {
          form.submit();
        }
      }
    }
  });

  if (captchaInput) {
    captchaInput.addEventListener('input', function () {
      this.value = this.value.toUpperCase();
    });
    captchaInput.addEventListener('input', function () {
      if (this.value.length === 4) {
        setTimeout(() => {
          const form = this.closest('form');
          if (form) {
            form.submit();
          }
        }, 500);
      }
    });
  }

  function showError(msg) {
    if (errorMessage) {
      errorMessage.textContent = msg;
      errorMessage.classList.add('show');
      setTimeout(function () {
        errorMessage.classList.remove('show');
      }, 3000);
    } else {
      alert(msg);
    }
  }
  const captchaImg = document.getElementById('captcha-img');
  if (captchaImg) {
    captchaImg.addEventListener('click', refreshCaptcha);
  }
  const formInputs = document.querySelectorAll('.login-form input');
  formInputs.forEach(input => {
    if (input.value.trim() !== '') {
      input.classList.add('has-value');
    }
    input.addEventListener('input', function () {
      if (this.value.trim() !== '') {
        this.classList.add('has-value');
      } else {
        this.classList.remove('has-value');
      }
    });
  });
});

function refreshCaptcha() {
  const captchaImg = document.getElementById('captcha-img');
  const captchaInput = document.getElementById('captcha');

  if (captchaImg) {
    captchaImg.src = 'captcha.php?' + new Date().getTime();
    if (captchaInput) {
      captchaInput.value = '';
      captchaInput.focus();
    }
    const refreshBtn = document.querySelector('.refresh-captcha i');
    if (refreshBtn) {
      refreshBtn.style.transform = 'rotate(360deg)';
      setTimeout(() => {
        refreshBtn.style.transform = '';
      }, 300);
    }
  }
}

function formatTime(seconds) {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;

  if (minutes > 0) {
    return `${minutes}分${remainingSeconds}秒`;
  } else {
    return `${remainingSeconds}秒`;
  }
}
function startCountdown(remainingSeconds) {
  const errorElement = document.getElementById('errorMessage');
  const submitBtn = document.querySelector('button[type="submit"]');

  if (!errorElement) return;

  const interval = setInterval(() => {
    remainingSeconds--;

    if (remainingSeconds <= 0) {
      clearInterval(interval);

      window.location.reload();
    } else {
      const minutes = Math.ceil(remainingSeconds / 60);
      errorElement.textContent = `登录失败次数过多，请等待 ${minutes} 分钟后再试`;
      if (submitBtn) {
        submitBtn.disabled = true;
      }
    }
  }, 1000);
} 