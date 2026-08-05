<?php
session_start();
require 'conexion.php';

if (isset($_GET['id'])) {
    try {
        $pdo->prepare("DELETE FROM vehiculos WHERE id_vehiculo = ?")->execute([$_GET['id']]);
    } catch (PDOException $e) {
        die("Error al eliminar: " . $e->getMessage());
    }
}
header("Location: menu.php");
exit;
?>