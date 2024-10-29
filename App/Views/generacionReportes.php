<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

// Conexión a la base de datos
require_once __DIR__ . '/../../Config/DataBase.php'; // Corregir la ruta aquí
$db = new DataBase();
$conn = $db->getConnection();

// Obtener reportes de la base de datos
$queryRoles = "SELECT id, colaborador_id, accion, id_rol, fecha FROM reportes";
$stmtRoles = $conn->prepare($queryRoles);
$stmtRoles->execute();
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);
?>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
              <div class="card">
                <h5 class="card-header">Reportes</h5>
                <div class="table-responsive text-nowrap">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>id</th>
                        <th>Nombre</th>
                        <th>Accion</th>
                        <th>Rol</th>
                        <th>Fecha</th>
                      </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
    
                      <tr>
                        <td><i class="fab fa-react fa-lg text-info me-3"></i> <strong>ejemplo</strong></td>
                        <td>ejemplo</td>
                        <td>ejemplo</td>
                        <td><span class="badge bg-label-success me-1">ejemploRol</span></td>
                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                              <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                              <a class="dropdown-item" href="javascript:void(0);"
                                ><i class="bx bx-edit-alt me-2"></i> Editar</a
                              >
                              <a class="dropdown-item" href="javascript:void(0);"
                                ><i class="bx bx-trash me-2"></i>Eliminar</a
                              >
                            </div>
                          </div>
                        </td>
                      </tr>
                      
               
                    </tbody>
                  </table>
                </div>
              </div>
              <!--/ Basic Bootstrap Table -->
              </div>
            </div>



<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!file_exists($footerPath)) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>