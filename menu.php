<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    if ($_SESSION['rol'] == 'conductor') header("Location: panel_conductor.php");
    elseif ($_SESSION['rol'] == 'pasajero') header("Location: panel_pasajero.php");
    else header("Location: login.php");
    exit;
}

require 'conexion.php';

$viajes_pendientes = $pdo->query("SELECT COUNT(*) FROM viajes WHERE estado_viaje = 'solicitado'")->fetchColumn();
$viajes_curso = $pdo->query("SELECT COUNT(*) FROM viajes WHERE estado_viaje = 'en_curso'")->fetchColumn();
$choferes_libres = $pdo->query("SELECT COUNT(*) FROM conductores WHERE estado_conductor = 'disponible'")->fetchColumn();
$autos_taller = $pdo->query("SELECT COUNT(*) FROM vehiculos WHERE estado_vehiculo = 'taller'")->fetchColumn();

$sql_conductores = "SELECT c.id_conductor, u.nombre_completo, c.dni, c.licencia, c.telefono, c.estado_conductor 
                    FROM conductores c INNER JOIN usuarios u ON c.id_usuario = u.id_usuario";
$lista_conductores = $pdo->query($sql_conductores)->fetchAll(PDO::FETCH_ASSOC);

$lista_vehiculos = $pdo->query("SELECT * FROM vehiculos")->fetchAll(PDO::FETCH_ASSOC);

$sql_pendientes = "SELECT v.id_viaje, u.nombre_completo AS pasajero, v.origen, v.destino, v.fecha_solicitud 
                   FROM viajes v INNER JOIN usuarios u ON v.id_pasajero = u.id_usuario 
                   WHERE v.estado_viaje = 'solicitado'";
$lista_pendientes = $pdo->query($sql_pendientes)->fetchAll(PDO::FETCH_ASSOC);

$sql_historial = "SELECT v.id_viaje, u.nombre_completo AS pasajero, v.origen, v.destino, 
                         c.dni AS conductor_dni, veh.placa AS vehiculo_placa, v.estado_viaje 
                  FROM viajes v 
                  INNER JOIN usuarios u ON v.id_pasajero = u.id_usuario 
                  LEFT JOIN conductores c ON v.id_conductor = c.id_conductor
                  LEFT JOIN vehiculos veh ON v.id_vehiculo = veh.id_vehiculo
                  ORDER BY v.id_viaje DESC";
$lista_historial = $pdo->query($sql_historial)->fetchAll(PDO::FETCH_ASSOC);

$conductores_disponibles = $pdo->query("SELECT id_conductor, dni, licencia FROM conductores WHERE estado_conductor = 'disponible'")->fetchAll(PDO::FETCH_ASSOC);
$vehiculos_disponibles = $pdo->query("SELECT id_vehiculo, modelo, placa FROM vehiculos WHERE estado_vehiculo = 'activo'")->fetchAll(PDO::FETCH_ASSOC);

$c_edit = ['id_conductor'=>'', 'nombre_completo'=>'', 'dni'=>'', 'licencia'=>'', 'telefono'=>''];
if(isset($_GET['edit_conductor'])){
    $stmt = $pdo->prepare("SELECT c.id_conductor, u.nombre_completo, c.dni, c.licencia, c.telefono FROM conductores c INNER JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE c.id_conductor = ?");
    $stmt->execute([$_GET['edit_conductor']]);
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) $c_edit = $row;
}

$v_edit = ['id_vehiculo'=>'', 'modelo'=>'', 'placa'=>'', 'anio'=>'', 'estado_vehiculo'=>'activo'];
if(isset($_GET['edit_vehiculo'])){
    $stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE id_vehiculo=?");
    $stmt->execute([$_GET['edit_vehiculo']]);
    if($row = $stmt->fetch(PDO::FETCH_ASSOC)) $v_edit = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Control de Taxis - Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="sidebar d-flex flex-column justify-content-between">
    <div>
      <div class="user-profile text-center">
        <div class="fw-bold text-white p-3 bg-dark border-bottom border-secondary">
            Bienvenido,<br> <?= htmlspecialchars($_SESSION['nombre_completo']); ?>
        </div>
        <span class="badge bg-primary mt-2">Rol: <?= htmlspecialchars($_SESSION['rol']); ?></span>
      </div>

      <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
        <button class="nav-link active" id="tab-dashboard" data-bs-toggle="tab" data-bs-target="#sec-dashboard" type="button">Panel de Control</button>
        <button class="nav-link" id="tab-pendientes" data-bs-toggle="tab" data-bs-target="#sec-pendientes" type="button">Viajes Pendientes</button>
        <button class="nav-link" id="tab-conductores" data-bs-toggle="tab" data-bs-target="#sec-conductores" type="button">Gestionar Conductores</button>
        <button class="nav-link" id="tab-vehiculos" data-bs-toggle="tab" data-bs-target="#sec-vehiculos" type="button">Gestionar Vehículos</button>
        <button class="nav-link" id="tab-historial" data-bs-toggle="tab" data-bs-target="#sec-historial" type="button">Historial General</button>
      </div>
    </div>

    <div class="logout-section p-3 text-center">
        <small class="text-secondary mb-2 d-block">Desarrollado por Josemanuel Cantorin<br>Taller Senati</small>
        <a href="logout.php" class="nav-link text-danger fw-bold py-2">Cerrar sesión</a>
    </div>
  </div>

  <div class="content-area p-4">
    <header class="text-center py-4 bg-white shadow-sm mb-5 rounded">
      <div class="container">
        <h1 class="display-5 fw-bold text-primary m-0">SISTEMA DE TAXIS</h1>
      </div>
    </header>

    <div class="tab-content" id="v-pills-tabContent">
      
      <div class="tab-pane fade show active" id="sec-dashboard" role="tabpanel">
        <h2 class="mb-4 fw-bold text-secondary">Resumen del Estado Actual</h2>
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="card shadow border-0 rounded bg-white text-center h-100">
              <div class="card-body p-4">
                <h6 class="text-muted fw-bold text-uppercase small">Viajes Solicitados</h6>
                <h2 class="fw-bold text-dark m-0"><?= $viajes_pendientes ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card shadow border-0 rounded bg-white text-center h-100">
              <div class="card-body p-4">
                <h6 class="text-muted fw-bold text-uppercase small">En Curso</h6>
                <h2 class="fw-bold text-dark m-0"><?= $viajes_curso ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card shadow border-0 rounded bg-white text-center h-100">
              <div class="card-body p-4">
                <h6 class="text-muted fw-bold text-uppercase small">Choferes Libres</h6>
                <h2 class="fw-bold text-dark m-0"><?= $choferes_libres ?></h2>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="card shadow border-0 rounded bg-white text-center h-100">
              <div class="card-body p-4">
                <h6 class="text-muted fw-bold text-uppercase small">Autos en Taller</h6>
                <h2 class="fw-bold text-dark m-0"><?= $autos_taller ?></h2>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="sec-pendientes" role="tabpanel">
        <h2 class="mb-4 fw-bold text-secondary">Gestión de Solicitudes</h2>
        
        <div class="card shadow border-0 rounded mb-4 bg-white">
          <div class="card-header bg-dark text-white fw-bold">Solicitudes de Pasajeros por Atender</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Pasajero</th>
                    <th>Fecha/Hora Solicitud</th>
                    <th>Ruta (Origen -> Destino)</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lista_pendientes as $pendiente): ?>
                  <tr>
                    <td><?= $pendiente['id_viaje'] ?></td>
                    <td><?= htmlspecialchars($pendiente['pasajero']) ?></td>
                    <td><?= htmlspecialchars($pendiente['fecha_solicitud']) ?></td>
                    <td><?= htmlspecialchars($pendiente['origen']) ?> -> <?= htmlspecialchars($pendiente['destino']) ?></td>
                    <td><button class="btn btn-sm btn-success fw-bold">Seleccionar</button></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($lista_pendientes)): ?>
                  <tr><td colspan="5" class="text-center py-3">No hay viajes pendientes por despachar.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <form action="procesar_asignacion.php" method="POST">
          <div class="card shadow border-0 rounded mb-4 bg-white">
            <div class="card-header bg-primary text-white fw-bold py-3">Gestión de Viaje / Oferta</div>
            <div class="card-body p-4 row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-primary">Asignar Conductor Disponible</label>
                <select name="id_conductor" class="form-select border-primary" required>
                  <option value="">-- Seleccionar Chofer --</option>
                  <?php foreach($conductores_disponibles as $cd): ?>
                    <option value="<?= $cd['id_conductor'] ?>">DNI: <?= htmlspecialchars($cd['dni']) ?> (Licencia: <?= htmlspecialchars($cd['licencia']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold text-primary">Asignar Vehículo Libre</label>
                <select name="id_vehiculo" class="form-select border-primary" required>
                  <option value="">-- Seleccionar Auto --</option>
                  <?php foreach($vehiculos_disponibles as $vd): ?>
                    <option value="<?= $vd['id_vehiculo'] ?>"><?= htmlspecialchars($vd['modelo']) ?> (Placa: <?= htmlspecialchars($vd['placa']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-12 mb-3">
                <label class="form-label fw-bold text-primary">Tarifa Acordada (S/.)</label>
                <input type="number" step="0.01" name="tarifa_estimada" class="form-control border-primary" required placeholder="0.00">
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
              <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold" <?= empty($lista_pendientes) ? 'disabled' : '' ?>>CONFIRMAR VIAJE</button>
            </div>
          </div>
        </form>
      </div>

      <div class="tab-pane fade <?= isset($_GET['edit_conductor']) ? 'show active' : '' ?>" id="sec-conductores" role="tabpanel">
        <h2 class="mb-4 fw-bold text-secondary">Administración de Conductores</h2>
        
        <form action="procesar_conductor.php" method="POST" class="mb-5">
          <input type="hidden" name="id_conductor" value="<?= $c_edit['id_conductor'] ?>">
          
          <div class="card shadow border-0 rounded bg-white">
            <div class="card-header bg-primary text-white fw-bold py-3">
              <?= empty($c_edit['id_conductor']) ? 'Registrar Nuevo Conductor' : 'Modificando Conductor: ' . htmlspecialchars($c_edit['nombre_completo']) ?>
            </div>
            <div class="card-body p-4 row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Nombre Completo</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Carlos Mendoza" value="<?= htmlspecialchars($c_edit['nombre_completo']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">DNI</label>
                <input type="text" name="dni" class="form-control" required placeholder="8 dígitos" value="<?= htmlspecialchars($c_edit['dni']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">N° de Licencia</label>
                <input type="text" name="licencia" class="form-control" required placeholder="Ej: Q12345678" value="<?= htmlspecialchars($c_edit['licencia']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Teléfono</label>
                <input type="text" name="telefono" class="form-control" required placeholder="Ej: 987654321" value="<?= htmlspecialchars($c_edit['telefono']) ?>">
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end pe-4 pb-4">
              <?php if($c_edit['id_conductor']): ?>
                <a href="menu.php" class="btn btn-secondary fw-bold px-4 me-2">Cancelar</a>
              <?php endif; ?>
              <button type="submit" class="btn btn-primary fw-bold px-4"><?= empty($c_edit['id_conductor']) ? 'Guardar Conductor' : 'Actualizar Conductor' ?></button>
            </div>
          </div>
        </form>

        <div class="card shadow border-0 rounded bg-white">
          <div class="card-header bg-dark text-white fw-bold">Conductores Registrados en el Sistema</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>DNI</th>
                    <th>Licencia</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lista_conductores as $cond): ?>
                  <tr>
                    <td><?= $cond['id_conductor'] ?></td>
                    <td><?= htmlspecialchars($cond['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($cond['dni']) ?></td>
                    <td><?= htmlspecialchars($cond['licencia']) ?></td>
                    <td><?= htmlspecialchars($cond['telefono']) ?></td>
                    <td>
                        <?php if($cond['estado_conductor'] == 'disponible'): ?>
                            <span class="badge bg-success">Disponible</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($cond['estado_conductor']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                      <a href="menu.php?edit_conductor=<?= $cond['id_conductor'] ?>" class="btn btn-sm btn-warning fw-bold me-1">Editar</a>
                      <a href="eliminar_conductor.php?id=<?= $cond['id_conductor'] ?>" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('¿Seguro que deseas eliminar a este conductor?');">Eliminar</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($lista_conductores)): ?>
                  <tr><td colspan="7" class="text-center py-3">No hay conductores registrados.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade <?= isset($_GET['edit_vehiculo']) ? 'show active' : '' ?>" id="sec-vehiculos" role="tabpanel">
        <h2 class="mb-4 fw-bold text-secondary">Administración de la Flota</h2>
        
        <form action="procesar_vehiculo.php" method="POST" class="mb-5">
          <input type="hidden" name="id_vehiculo" value="<?= $v_edit['id_vehiculo'] ?>">
          
          <div class="card shadow border-0 rounded bg-white">
            <div class="card-header bg-primary text-white fw-bold py-3">
               <?= empty($v_edit['id_vehiculo']) ? 'Registrar Nuevo Vehículo' : 'Modificando Vehículo: ' . htmlspecialchars($v_edit['placa']) ?>
            </div>
            <div class="card-body p-4 row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Marca y Modelo</label>
                <input type="text" name="modelo" class="form-control" required placeholder="Ej: Toyota Corolla" value="<?= htmlspecialchars($v_edit['modelo']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Placa / Matrícula</label>
                <input type="text" name="placa" class="form-control" required placeholder="Ej: ABC-123" value="<?= htmlspecialchars($v_edit['placa']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Año</label>
                <input type="number" name="anio" class="form-control" required placeholder="Ej: 2024" value="<?= htmlspecialchars($v_edit['anio']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Estado Inicial</label>
                <select name="estado_vehiculo" class="form-select" required>
                  <option value="activo" <?= $v_edit['estado_vehiculo'] == 'activo' ? 'selected' : '' ?>>Disponible / Activo</option>
                  <option value="taller" <?= $v_edit['estado_vehiculo'] == 'taller' ? 'selected' : '' ?>>En Taller / Mantenimiento</option>
                </select>
              </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end pe-4 pb-4">
              <?php if($v_edit['id_vehiculo']): ?>
                <a href="menu.php" class="btn btn-secondary fw-bold px-4 me-2">Cancelar</a>
              <?php endif; ?>
              <button type="submit" class="btn btn-primary fw-bold px-4"><?= empty($v_edit['id_vehiculo']) ? 'Guardar Vehículo' : 'Actualizar Vehículo' ?></button>
            </div>
          </div>
        </form>

        <div class="card shadow border-0 rounded bg-white">
          <div class="card-header bg-dark text-white fw-bold">Vehículos en la Flota</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Marca/Modelo</th>
                    <th>Placa</th>
                    <th>Año</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lista_vehiculos as $vehiculo): ?>
                  <tr>
                    <td><?= htmlspecialchars($vehiculo['modelo']) ?></td>
                    <td><?= htmlspecialchars($vehiculo['placa']) ?></td>
                    <td><?= htmlspecialchars($vehiculo['anio']) ?></td>
                    <td>
                        <?php if($vehiculo['estado_vehiculo'] == 'activo'): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php elseif($vehiculo['estado_vehiculo'] == 'taller'): ?>
                            <span class="badge bg-warning text-dark">En Taller</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                      <a href="menu.php?edit_vehiculo=<?= $vehiculo['id_vehiculo'] ?>" class="btn btn-sm btn-warning fw-bold me-1">Editar</a>
                      <a href="eliminar_vehiculo.php?id=<?= $vehiculo['id_vehiculo'] ?>" class="btn btn-sm btn-danger fw-bold" onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">Eliminar</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($lista_vehiculos)): ?>
                  <tr><td colspan="5" class="text-center py-3">No hay vehículos registrados.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div class="tab-pane fade" id="sec-historial" role="tabpanel">
        <h2 class="mb-4 fw-bold text-secondary">Registro de Actividad</h2>
        <div class="card shadow border-0 rounded bg-white">
          <div class="card-header bg-primary text-white fw-bold py-3">Historial General de Servicios Solicitados</div>
          <div class="card-body p-4">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Pasajero</th>
                    <th>Origen / Destino</th>
                    <th>DNI Conductor</th>
                    <th>Placa Vehículo</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lista_historial as $hist): ?>
                  <tr>
                    <td><?= $hist['id_viaje'] ?></td>
                    <td><?= htmlspecialchars($hist['pasajero']) ?></td>
                    <td><?= htmlspecialchars($hist['origen']) ?> -> <?= htmlspecialchars($hist['destino']) ?></td>
                    <td><?= $hist['conductor_dni'] ? htmlspecialchars($hist['conductor_dni']) : 'N/A' ?></td>
                    <td><?= $hist['vehiculo_placa'] ? htmlspecialchars($hist['vehiculo_placa']) : 'N/A' ?></td>
                    <td>
                        <?php if($hist['estado_viaje'] == 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                        <?php elseif($hist['estado_viaje'] == 'solicitado'): ?>
                            <span class="badge bg-primary">Solicitado</span>
                        <?php elseif($hist['estado_viaje'] == 'en_curso'): ?>
                            <span class="badge bg-warning text-dark">En Curso</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= ucfirst($hist['estado_viaje']) ?></span>
                        <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($lista_historial)): ?>
                  <tr><td colspan="6" class="text-center py-3">No hay historial de viajes.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      <?php if(isset($_GET['edit_conductor'])): ?>
        var tab = new bootstrap.Tab(document.querySelector('#tab-conductores'));
        tab.show();
      <?php elseif(isset($_GET['edit_vehiculo'])): ?>
        var tab = new bootstrap.Tab(document.querySelector('#tab-vehiculos'));
        tab.show();
      <?php endif; ?>
    });
  </script>
</body>
</html>