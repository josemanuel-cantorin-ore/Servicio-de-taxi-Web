<?php
session_start();
require 'conexion.php';

if (isset($_GET['id'])) {
    try {
        // Como están vinculados, borramos el usuario y el CASCADE borrará al conductor
        $sql = "DELETE FROM usuarios WHERE id_usuario = (SELECT id_usuario FROM conductores WHERE id_conductor = ?)";
        $pdo->prepare($sql)->execute([$_GET['id']]);
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
}
header("Location: menu.php");
exit;
?>