// مدیریت احراز هویت
document.addEventListener('DOMContentLoaded', function() {
    // نمایش/مخفی کردن رمز عبور
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        const passwordToggle = document.querySelector('.password-toggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    passwordToggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    passwordToggle.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        }
    }

    // اعتبارسنجی فرم لاگین
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            let isValid = true;

            // اعتبارسنجی سمت کلاینت
            if (!username.value.trim()) {
                username.style.borderColor = '#f44336';
                isValid = false;
            } else {
                username.style.borderColor = '#ddd';
            }

            if (!password.value.trim()) {
                password.style.borderColor = '#f44336';
                isValid = false;
            } else {
                password.style.borderColor = '#ddd';
            }

            if (!isValid) {
                event.preventDefault();
                showNotification('لطفا تمام فیلدها را پر کنید', 'error');
            }
        });
    }

    // تابع نمایش نوتیفیکیشن (اگر وجود ندارد)
    if (typeof showNotification === 'undefined') {
        window.showNotification = function(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        };
    }
});