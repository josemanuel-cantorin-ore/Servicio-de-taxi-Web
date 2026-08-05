<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['rol'] !== 'pasajero') {
    header("Location: login.php");
    exit;
}
require 'conexion.php';
$id_pasajero = $_SESSION['id_usuario'];

$sql = "SELECT v.id_viaje, v.origen, v.destino, v.tarifa_final, v.estado_viaje, 
               u.nombre_completo AS conductor, veh.placa 
        FROM viajes v 
        LEFT JOIN conductores c ON v.id_conductor = c.id_conductor
        LEFT JOIN usuarios u ON c.id_usuario = u.id_usuario
        LEFT JOIN vehiculos veh ON v.id_vehiculo = veh.id_vehiculo
        WHERE v.id_pasajero = ? ORDER BY v.id_viaje DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pasajero]);
$mis_viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Panel - Pasajero</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-dark shadow">
    <div class="container">
      <span class="navbar-brand fw-bold">Sistema de Taxis - Pasajero</span>
      <div class="d-flex align-items-center">
        <span class="text-white me-3">Hola, <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
        <a href="logout.php" class="btn btn-sm btn-danger fw-bold">Salir</a>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="row">
      <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 border-top border-success border-4">
          <div class="card-header bg-white fw-bold">Solicitar un Viaje</div>
          <div class="card-body">
            <form action="procesar_solicitud.php" method="POST">
              <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">Origen</label>
                <input type="text" name="origen" class="form-control" required placeholder="¿Dónde estás?">
              </div>
              <div class="mb-4">
                <label class="form-label text-secondary small fw-bold">Destino</label>
                <input type="text" name="destino" class="form-control" required placeholder="¿A dónde vas?">
              </div>
              <button type="submit" class="btn btn-success w-100 fw-bold">Pedir Taxi Ahora</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card shadow-sm border-0">
          <div class="card-header bg-white fw-bold">Mis Viajes</div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-light">
                  <tr>
                    <th>Ruta</th>
                    <th>Precio a Pagar</th>
                    <th>Conductor y Auto</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($mis_viajes as $v): ?>
                  <tr>
                    <td><small><?= htmlspecialchars($v['origen']) ?><br>↓<br><?= htmlspecialchars($v['destino']) ?></small></td>
                    <td class="fw-bold text-success">
                      <?= $v['tarifa_final'] > 0 ? 'S/. ' . $v['tarifa_final'] : '<span class="text-muted small">Por definir</span>' ?>
                    </td>
                    <td>
                      <?php if($v['conductor']): ?>
                        <?= htmlspecialchars($v['conductor']) ?><br><small class="text-muted"><?= htmlspecialchars($v['placa']) ?></small>
                      <?php else: ?>
                        <span class="text-muted fst-italic">Buscando...</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if($v['estado_viaje'] == 'solicitado'): ?>
                        <span class="badge bg-primary">Buscando Chofer</span>
                      <?php elseif($v['estado_viaje'] == 'en_curso'): ?>
                        <span class="badge bg-warning text-dark">En Curso</span>
                      <?php else: ?>
                        <span class="badge bg-success">Completado</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($mis_viajes)): ?>
                  <tr><td colspan="4" class="py-4 text-muted">Aún no has solicitado ningún viaje.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>