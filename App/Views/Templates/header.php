<?php
require_once __DIR__ . '/../../../config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Iniciar sesión para acceder a los datos de la sesión
}

// Obtener el rol del usuario desde la sesión
$rol = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
?>
<!DOCTYPE html>
<html lang="es" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-Public-path="../Public/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Dashboard </title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>Public/img/favicon/favicon.ico" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/vendor/css/core.css"
    class="template-customizer-core-css" />
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/vendor/css/theme-default.css"
    class="template-customizer-theme-css" />
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Public/vendor/libs/apex-charts/apex-charts.css" />
  <!-- Helpers -->
  <script src="<?php echo BASE_URL; ?>Public/vendor/js/helpers.js"></script>
  <!-- Config -->
  <script src="<?php echo BASE_URL; ?>Public/js/config.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="./dashboard" class="app-brand-link">
            <span class="app-brand-logo demo">
              <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
                <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                    <g id="Icon" transform="translate(27.000000, 15.000000)">
                      <g id="Mask" transform="translate(0.000000, 8.000000)">
                        <mask id="mask-2" fill="white">
                          <use xlink:href="#path-1"></use>
                        </mask>
                        <use fill="#696cff" xlink:href="#path-1"></use>
                        <g id="Path-3" mask="url(#mask-2)">
                          <use fill="#696cff" xlink:href="#path-3"></use>
                          <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                        </g>
                        <g id="Path-4" mask="url(#mask-2)">
                          <use fill="#696cff" xlink:href="#path-4"></use>
                          <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                        </g>
                      </g>
                      <g id="Triangle"
                        transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                        <use fill="#696cff" xlink:href="#path-5"></use>
                        <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                      </g>
                    </g>
                  </g>
                </g>
              </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Clinica Brrr</span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
          <!-- Dashboard -->
          <li class="menu-item active">
            <a href="./dashboard" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Inicio</div>
            </a>
          </li>
          
   <!--INICIO DEL MENU DEL PACIENTE -->
   <?php if ($rol === 'paciente'): ?>
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">Paciente</span>
                        </li>
                        <li class="menu-item">
                            <a href="./agendarCita" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Agendar Citas">Agendar Citas</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="./verCitas" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-lock-open-alt"></i>
                                <div data-i18n="Authentications">Ver citas</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="./verHistorialMedico" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                                <div data-i18n="Misc">Historial medico</div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <!--FIN DEL MENU DEL PACIENTE -->

                    <!--INICIO DEL MENU DEL ADMINISTRADOR -->
                    <?php if ($rol === 'administrador'): ?>
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">Administrador</span>
                        </li>
                        <li class="menu-item">
                            <a href="./actualizarInformacionUsuarios" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-collection"></i>
                                <div data-i18n="Basic">Editar Usuarios</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="#" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-box"></i>
                                <div data-i18n="User interface">Reportes</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="./gestionInventario" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-copy"></i>
                                <div data-i18n="Extended UI">Inventario</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="#" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-crown"></i>
                                <div data-i18n="Boxicons">Auditorias</div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <!--FIN DEL MENU DEL ADMINISTRADOR -->

                    <!--INICIO DEL MENU DEL MEDICO -->
                    <?php if ($rol === 'medico'): ?>
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">Medico</span>
                        </li>
                        <li class="menu-item">
                            <a href="./calendarioCitasMedico" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Agendar Citas">Calendario de citas</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="./agendarCitaMedico" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Agendar Citas">Agendar Citas para pacientes</div>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="./verCitasMedico" class="menu-link">
                                <i class="menu-icon tf-icons bx bx-calendar"></i>
                                <div data-i18n="Agendar Citas">Ver citas</div>
                            </a>
                        </li>
                    <?php endif; ?>
                    <!--FIN DEL MENU DEL MEDICO -->
        </ul>
      </aside>
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
              <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input type="text" class="form-control border-0 shadow-none" placeholder="Buscar..."
                  aria-label="Buscar..." />
              </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Place this tag where you want the button to render. -->

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="<?php echo BASE_URL; ?>Public/img/avatars/1.png" alt
                      class="w-px-40 h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="<?php echo BASE_URL; ?>Public/img/avatars/1.png" alt
                              class="w-px-40 h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block">
                            <?php
                            echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Nombre no disponible';
                            echo ' ';
                            echo isset($_SESSION['apellido']) ? $_SESSION['apellido'] : 'Apellido no disponible';
                            ?>
                          </span>
                          <small class="text-muted">
                            <?php echo isset($_SESSION['rol']) ? $_SESSION['rol'] : 'Rol no disponible'; ?>
                          </small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="./configuracionCuenta">
                      <i class="bx bx-user me-2"></i>
                      <span class="align-middle">Perfil</span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="./logout">
                      <i class="bx bx-power-off me-2"></i>
                      <span class="align-middle">Cerrar sesión</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </nav>

        <!-- / Navbar -->