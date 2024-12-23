document.querySelectorAll('.toggle-password').forEach(function(element) {
    element.addEventListener('click', function() {
        const passwordInput = this.previousElementSibling;
        if (passwordInput.type === 'text') {
            passwordInput.type = 'password';
            this.classList.add('fa-eye-slash');
            this.classList.remove('fa-eye');

        } else {
            passwordInput.type = 'text';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
});