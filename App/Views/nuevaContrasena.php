<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay un token válido
if (!isset($_GET['token'])) {
    header('Location: ./login');
    exit();
}

$token = $_GET['token'];

// Verificar rutas
$headerPath = __DIR__ . '/Templates/headerAuth.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo headerAuth.php en la ruta especificada.');
}
include $headerPath;
?>

<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center">
                        <a href="#" class="app-brand-link gap-2">
                            <span class="app-brand-text demo text-body fw-bolder">medisfera</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-2">Restablecer Contraseña 🔒</h4>
                    <p class="mb-4">Ingresa tu nueva contraseña</p>

                    <form id="formAuthentication" class="mb-3" action="./actualizarContrasena" method="POST">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Nueva Contraseña</label>
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="password"
                                    class="form-control"
                                    name="password"
                                    placeholder="············"
                                    required
                                />
                                <span class="input-group-text cursor-pointer">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="confirm-password">Confirmar Contraseña</label>
                            <div class="input-group input-group-merge">
                                <input
                                    type="password"
                                    id="confirm-password"
                                    class="form-control"
                                    name="confirm-password"
                                    placeholder="············"
                                    required
                                />
                                <span class="input-group-text cursor-pointer">
                                    <i class="bx bx-hide"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary d-grid w-100">
                            Establecer nueva contraseña
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="./login" class="d-flex align-items-center justify-content-center">
                            <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                            Volver a inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$footerPath = __DIR__ . '/Templates/footerAuth.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footerAuth.php en la ruta especificada.');
}
require $footerPath;
?>

<script>
document.getElementById('formAuthentication').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
    }
});

// Función para alternar la visibilidad de la contraseña
document.querySelectorAll('.input-group-text').forEach(function(element) {
    element.addEventListener('click', function(e) {
        const input = this.previousElementSibling;
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        const icon = this.querySelector('i');
        icon.classList.toggle('bx-hide');
        icon.classList.toggle('bx-show');
    });
});
</script>