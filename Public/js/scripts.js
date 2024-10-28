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

// Función para mostrar campos adicionales
function mostrarCamposAdicionales() {
    const tipoUsuario = document.getElementById('tipoUsuario').value;
    const camposAdicionales = document.getElementById('camposAdicionales');
    if (tipoUsuario == 1 || tipoUsuario == 2) { // Asumiendo que los IDs de los roles son 1 y 2
        camposAdicionales.style.display = 'block';
    } else {
        camposAdicionales.style.display = 'none';
    }
}

// Función para enviar el formulario de agregar usuario
function submitAgregarUsuarioForm() {
    const form = $('#agregarUsuarioForm');
    if (form[0].checkValidity()) {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {
                const res = JSON.parse(response);
                if (res.success) {
                    alert(res.message);
                    if (res.message === "Usuario registrado correctamente.") {
                        window.location.reload(); // Recargar la página
                    } else {
                        $('#agregarUsuarioModal').modal('hide');
                        actualizarTablaUsuarios(); // Recargar la tabla de usuarios
                    }
                } else {
                    alert(res.message);
                }
            }
        });
    } else {
        form[0].reportValidity();
    }
}

// Función para actualizar la tabla de usuarios
function actualizarTablaUsuarios() {
    $.ajax({
        url: './obtenerUsuariosPaginados',
        type: 'GET',
        success: function (response) {
            const usuarios = JSON.parse(response);
            const tbody = $('.table tbody');
            tbody.empty();
            usuarios.forEach(usuario => {
                tbody.append(`
                    <tr>
                        <td>${usuario.id}</td>
                        <td>${usuario.nombre || 'Nombre no disponible'}</td>
                        <td>${usuario.apellido || 'Apellido no disponible'}</td>
                        <td>${usuario.email}</td>
                        <td>${usuario.rol || 'Rol no disponible'}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="editarUsuario(${usuario.id})">
                                        <i class="bx bx-edit-alt me-2"></i> Editar
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarUsuario(${usuario.id})">
                                        <i class="bx bx-trash me-2"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }
    });
}

