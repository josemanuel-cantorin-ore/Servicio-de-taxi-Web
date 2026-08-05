<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pasajero = $_SESSION['id_usuario']; 
    $origen = trim($_POST['origen']);
    $destino = trim($_POST['destino']);

    try {
        $sql = "INSERT INTO viajes (id_pasajero, origen, destino, tarifa_propuesta, estado_viaje) 
                VALUES (?, ?, ?, 0.00, 'solicitado')";
        $pdo->prepare($sql)->execute([$id_pasajero, $origen, $destino]);
        
        header("Location: panel_pasajero.php");
        exit;
    } catch (PDOException $e) {
        die("Error al registrar solicitud: " . $e->getMessage());
    }
}
?>