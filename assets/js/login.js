const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('reg-form');
const showRegister = document.getElementById('show-reg');
const showLogin = document.getElementById('show-login');

showRegister.addEventListener('click', () => {
    // Start slide-out animation
    loginForm.classList.add('slide-out');
    
    // Wait for the slide-out to finish before switching
    setTimeout(() => {
        loginForm.classList.add('hidden');
        loginForm.classList.remove('slide-out');

        registerForm.classList.remove('hidden');
        registerForm.classList.remove('slide-out'); // clean slate
        registerForm.classList.add('slide-in');

        // Remove slide-in after animation
        setTimeout(() => {
            registerForm.classList.remove('slide-in');
        }, 300);
    }, 300);
});

showLogin.addEventListener('click', () => {
    registerForm.classList.add('slide-out');

    setTimeout(() => {
        registerForm.classList.add('hidden');
        registerForm.classList.remove('slide-out');

        loginForm.classList.remove('hidden');
        loginForm.classList.remove('slide-out');
        loginForm.classList.add('slide-in');

        setTimeout(() => {
            loginForm.classList.remove('slide-in');
        }, 300);
    }, 300);
});
