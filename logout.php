<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

// Lógica para que el botón "Cancelar" devuelva a cada usuario a SU propia pantalla
$pagina_volver = 'menu.php'; // Por defecto para admin
if ($_SESSION['rol'] == 'conductor') {
    $pagina_volver = 'panel_conductor.php';
} elseif ($_SESSION['rol'] == 'pasajero') {
    $pagina_volver = 'panel_pasajero.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmar Salida - Sistema de Taxis</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="height: 100vh;">

  <div class="card shadow border-warning" style="width: 100%; max-width: 450px;">
    <div class="card-header bg-warning text-dark fw-bold text-center py-3">
      CONFIRMAR CERRAR SESIÓN
    </div>
    <div class="card-body text-center p-4">
      <h5 class="card-title fw-bold">¿Estás seguro de que deseas salir?</h5>
      <p class="card-text mt-3 text-muted">
        Al cerrar sesión se finalizará tu acceso actual al sistema.
      </p>
      
      <div class="mt-4">
        <!-- El botón cancelar ahora usa la variable dinámica -->
        <a href="<?= $pagina_volver ?>" class="btn btn-secondary fw-bold px-4 mx-2">Cancelar</a>
        
        <a href="ejecutar_logout.php" class="btn btn-danger fw-bold px-4 mx-2">Sí, cerrar sesión</a>
      </div>
    </div>
  </div>

</body>
</html>