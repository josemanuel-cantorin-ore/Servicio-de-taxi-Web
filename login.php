<?php
session_start();

if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    if ($_SESSION['rol'] == 'pasajero') header("Location: panel_pasajero.php");
    elseif ($_SESSION['rol'] == 'admin') header("Location: menu.php");
    elseif ($_SESSION['rol'] == 'conductor') header("Location: panel_conductor.php");
    exit;
}

include 'conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_login'])) {
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contraseña'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = :usuario LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($user_data && $contrasena === $user_data['contrasena']) {
            
            if ($user_data['rol'] == 'admin') {
                $_SESSION['logueado'] = true;
                $_SESSION['id_usuario'] = $user_data['id_usuario'];
                $_SESSION['nombre_completo'] = $user_data['nombre_completo'];
                $_SESSION['rol'] = $user_data['rol'];
                header("Location: menu.php");
                exit;
            } 
            elseif ($user_data['rol'] == 'pasajero') {
                $_SESSION['logueado'] = true;
                $_SESSION['id_usuario'] = $user_data['id_usuario'];
                $_SESSION['nombre_completo'] = $user_data['nombre_completo'];
                $_SESSION['rol'] = $user_data['rol'];
                header("Location: panel_pasajero.php");
                exit;
            }
            elseif ($user_data['rol'] == 'conductor') {
                $_SESSION['logueado'] = true;
                $_SESSION['id_usuario'] = $user_data['id_usuario'];
                $_SESSION['nombre_completo'] = $user_data['nombre_completo'];
                $_SESSION['rol'] = $user_data['rol'];
                header("Location: panel_conductor.php");
                exit;
            } 
            else {
                $error_login = "Acceso denegado: Tu usuario no tiene un panel web asignado.";
            }

        } else {
            $error_login = "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        $error_login = "Error en el sistema.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Taxis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
        <h2 class="text-center mb-4 fw-bold text-primary">INICIO DE SESIÓN</h2>
        
        <?php if (isset($error_login)): ?>
            <div class="alert alert-danger py-2 text-center small"><?= $error_login ?></div>
        <?php endif; ?>
        
        <form action="" method="post">
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Usuario:</label>
                <input type="text" name="usuario" class="form-control"  required autocomplete="off">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold text-secondary">Contraseña:</label>
                <input type="password" name="contraseña" class="form-control" required>
            </div>
            <button type="submit" name="btn_login" class="btn btn-primary w-100 mt-2 fw-bold">Ingresar</button>
            <div class="text-center mt-3">
                <a href="registro.php" class="text-decoration-none small text-success fw-bold">¿No tienes cuenta? Regístrate aquí</a>
            </div>
        </form>
    </div>
    <div class="mt-4 text-muted small fw-bold">
        Taller de SENATI - Desarrollado por Josemanuel Cantorin
    </div>
</div>
</body>
</html>