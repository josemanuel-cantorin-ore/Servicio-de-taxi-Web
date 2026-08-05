<?php
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $correo = trim($_POST['correo']);

    try {
        // Insertamos con el rol fijo de 'pasajero'
        $sql = "INSERT INTO usuarios (nombre, contrasena, nombre_completo, correo, rol) VALUES (?, ?, ?, ?, 'pasajero')";
        $pdo->prepare($sql)->execute([$nombre_usuario, $contrasena, $nombre_completo, $correo]);
        
        $mensaje = "¡Registro exitoso! Ya puedes solicitar taxis.";
    } catch (PDOException $e) {
        $error = "Error: El usuario o correo ya existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Pasajeros - inDrive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
        <h3 class="text-center mb-4 fw-bold text-success">Regístrate como Pasajero</h3>
 
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success py-2 text-center small"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger py-2 text-center small"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Nombre Completo:</label>
                <input type="text" name="nombre_completo" class="form-control" placeholder="Ej: María Gómez" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Correo:</label>
                <input type="email" name="correo" class="form-control" placeholder="maria@email.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Usuario (Para login):</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Contraseña:</label>
                <input type="password" name="contrasena" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100 mt-2 fw-bold">Crear Cuenta</button>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none small">Volver al Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>