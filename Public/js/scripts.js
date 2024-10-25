// Public/js/scripts.js

// Función para mostrar u ocultar la contraseña
function togglePasswordVisibility() {
    var passwordField = document.getElementById('password');
    var toggleIcon = document.getElementById('togglePasswordIcon');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('bx-hide');
        toggleIcon.classList.add('bx-show');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('bx-show');
        toggleIcon.classList.add('bx-hide');
    }
}

// Función para validar el formulario de registro Y aceptar términos y condiciones
    function validateForm() {
        var terms = document.getElementById('terms-conditions');
        if (!terms.checked) {
            alert('Debe aceptar la política de privacidad y términos.');
            return false;
        }
        return true;
    }


    