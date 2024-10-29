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

// Función para mostrar u ocultar el campo de especialidad basado en el rol seleccionado
function toggleEspecialidad() {
    const rolColaborador = document.getElementById('rolColaborador').value;
    const especialidadDiv = document.getElementById('especialidadColaboradorDiv');
    if (rolColaborador == 2) { // Asumiendo que el ID del rol de medico es 2
        especialidadDiv.style.display = 'block';
    } else {
        especialidadDiv.style.display = 'none';
    }
}

// Función para enviar el formulario de agregar paciente
function submitAgregarPacienteForm() {
    const form = $('#agregarPacienteForm');
    if (form[0].checkValidity()) {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {
                try {
                    const res = JSON.parse(response);
                    if (res.success) {
                        alert(res.message);
                        if (res.message === "Usuario registrado correctamente.") {
                            window.location.reload(); // Recargar la página
                        } else {
                            $('#agregarPacienteModal').modal('hide');
                            actualizarTablaUsuarios(); // Recargar la tabla de usuarios
                        }
                    } else {
                        alert(res.message);
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    console.error("Response:", response);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    } else {
        form[0].reportValidity();
    }
}

/// Función para enviar el formulario de agregar colaborador
function submitAgregarColaboradorForm() {
    const form = $('#agregarColaboradorForm');
    if (form[0].checkValidity()) {
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {
                try {
                    const res = JSON.parse(response);
                    if (res.success) {
                        alert(res.message);
                        if (res.message === "Colaborador registrado correctamente.") {
                            window.location.reload(); // Recargar la página
                        } else {
                            $('#agregarColaboradorModal').modal('hide');
                            actualizarTablaUsuarios(); // Recargar la tabla de usuarios
                        }
                    } else {
                        alert(res.message);
                    }
                } catch (e) {
                    console.error("Error parsing response:", e);
                    console.error("Response:", response);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
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
            try {
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
            } catch (e) {
                console.error("Error parsing response:", e);
                console.error("Response:", response);
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", status, error);
        }
    });
}

