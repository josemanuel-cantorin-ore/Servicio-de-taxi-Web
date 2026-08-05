<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_vehiculo = $_POST['id_vehiculo'] ?? '';
    $modelo = trim($_POST['modelo']);
    $placa = trim($_POST['placa']);
    $anio = trim($_POST['anio']);
    $estado = trim($_POST['estado_vehiculo']);

    try {
        if (!empty($id_vehiculo)) {
            // === MODO EDITAR (UPDATE) ===
            $sql = "UPDATE vehiculos SET modelo=?, placa=?, anio=?, estado_vehiculo=? WHERE id_vehiculo=?";
            $pdo->prepare($sql)->execute([$modelo, $placa, $anio, $estado, $id_vehiculo]);
        } else {
            // === MODO CREAR (INSERT) ===
            $sql = "INSERT INTO vehiculos (modelo, placa, anio, color, estado_vehiculo) VALUES (?, ?, ?, 'No especificado', ?)";
            $pdo->prepare($sql)->execute([$modelo, $placa, $anio, $estado]);
        }
        header("Location: menu.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>