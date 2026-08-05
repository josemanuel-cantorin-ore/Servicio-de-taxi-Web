<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['rol'] !== 'conductor') {
    header("Location: login.php");
    exit;
}

require 'conexion.php';
$id_usuario = $_SESSION['id_usuario'];

$stmt_c = $pdo->prepare("SELECT id_conductor FROM conductores WHERE id_usuario = ?");
$stmt_c->execute([$id_usuario]);
$conductor = $stmt_c->fetch(PDO::FETCH_ASSOC);

if (!$conductor) {
    die("Error: Tu cuenta no está registrada correctamente en la tabla de conductores.");
}
$id_conductor = $conductor['id_conductor'];

if (isset($_GET['completar_viaje'])) {
    $id_viaje = $_GET['completar_viaje'];
    try {
        $pdo->prepare("UPDATE viajes SET estado_viaje = 'completado' WHERE id_viaje = ?")->execute([$id_viaje]);
        $pdo->prepare("UPDATE conductores SET estado_conductor = 'disponible' WHERE id_conductor = ?")->execute([$id_conductor]);
        header("Location: panel_conductor.php");
        exit;
    } catch(PDOException $e) {
        die("Error al completar el viaje: " . $e->getMessage());
    }
}

$sql = "SELECT v.id_viaje, v.origen, v.destino, v.tarifa_final, v.estado_viaje, u.nombre_completo AS pasajero 
        FROM viajes v 
        INNER JOIN usuarios u ON v.id_pasajero = u.id_usuario 
        WHERE v.id_conductor = ? ORDER BY v.id_viaje DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_conductor]);
$viajes_asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Conductor</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
  <nav class="navbar navbar-dark bg-dark shadow">
    <div class="container">
      <span class="navbar-brand fw-bold text-warning">Sistema de Taxis - Conductor</span>
      <div class="d-flex align-items-center">
        <span class="text-white me-3">Volante: <?= htmlspecialchars($_SESSION['nombre_completo']) ?></span>
        <a href="logout.php" class="btn btn-sm btn-danger fw-bold">Salir</a>
      </div>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="card shadow-sm border-0 border-top border-warning border-4">
      <div class="card-header bg-white fw-bold">Mis Viajes Asignados</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead class="table-light">
              <tr>
                <th>Pasajero</th>
                <th>Ruta</th>
                <th>Tarifa a Cobrar</th>
                <th>Estado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($viajes_asignados as $v): ?>
              <tr>
                <td class="fw-bold"><?= htmlspecialchars($v['pasajero']) ?></td>
                <td><small><?= htmlspecialchars($v['origen']) ?> <br>↓<br> <?= htmlspecialchars($v['destino']) ?></small></td>
                <td class="fw-bold text-success">S/. <?= $v['tarifa_final'] ?></td>
                <td>
                  <?php if($v['estado_viaje'] == 'en_curso'): ?>
                    <span class="badge bg-warning text-dark">En Curso</span>
                  <?php else: ?>
                    <span class="badge bg-success">Completado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($v['estado_viaje'] == 'en_curso'): ?>
                    <a href="panel_conductor.php?completar_viaje=<?= $v['id_viaje'] ?>" class="btn btn-sm btn-success fw-bold" onclick="return confirm('¿Confirmas que ya dejaste al pasajero en su destino?');">Finalizar Viaje</a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-secondary fw-bold" disabled>Finalizado</button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($viajes_asignados)): ?>
              <tr><td colspan="5" class="py-4 text-muted fw-bold">No tienes viajes asignados actualmente. Espera a que el administrador te despache uno.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>