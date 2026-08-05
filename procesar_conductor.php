<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_conductor = $_POST['id_conductor'] ?? '';
    $nombre_completo = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);
    $licencia = trim($_POST['licencia']);
    $telefono = trim($_POST['telefono']);

    try {
        if (!empty($id_conductor)) {
            // === MODO EDITAR (UPDATE) ===
            $pdo->prepare("UPDATE conductores SET dni=?, licencia=?, telefono=? WHERE id_conductor=?")
                ->execute([$dni, $licencia, $telefono, $id_conductor]);
            
            $pdo->prepare("UPDATE usuarios SET nombre_completo=? WHERE id_usuario = (SELECT id_usuario FROM conductores WHERE id_conductor=?)")
                ->execute([$nombre_completo, $id_conductor]);
        } else {
            // === MODO CREAR (INSERT) ===
            $pdo->beginTransaction();
            
            $stmt_usuario = $pdo->prepare("INSERT INTO usuarios (nombre, contrasena, nombre_completo, correo, rol) VALUES (?, ?, ?, ?, 'conductor')");
            $stmt_usuario->execute([$dni, $dni, $nombre_completo, $dni . "@empresa.com"]);
            $id_usuario = $pdo->lastInsertId();

            $stmt_conductor = $pdo->prepare("INSERT INTO conductores (id_usuario, dni, licencia, telefono) VALUES (?, ?, ?, ?)");
            $stmt_conductor->execute([$id_usuario, $dni, $licencia, $telefono]);
            
            $pdo->commit();
        }
        header("Location: menu.php");
        exit;
    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
}
?>