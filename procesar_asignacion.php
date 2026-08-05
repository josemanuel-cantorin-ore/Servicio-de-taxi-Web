<?php
session_start();
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_conductor = $_POST['id_conductor'];
    $id_vehiculo = $_POST['id_vehiculo'];
    $tarifa = $_POST['tarifa_estimada'];

    try {

        $sql_buscar = "SELECT id_viaje FROM viajes WHERE estado_viaje = 'solicitado' ORDER BY fecha_solicitud ASC LIMIT 1";
        $stmt_buscar = $pdo->query($sql_buscar);
        $viaje = $stmt_buscar->fetch(PDO::FETCH_ASSOC);

        if ($viaje) {
            $id_viaje = $viaje['id_viaje'];

            $sql_update = "UPDATE viajes 
                           SET id_conductor = :id_conductor, 
                               id_vehiculo = :id_vehiculo, 
                               tarifa_final = :tarifa, 
                               estado_viaje = 'en_curso',
                               fecha_inicio = NOW() 
                           WHERE id_viaje = :id_viaje";
            
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                'id_conductor' => $id_conductor,
                'id_vehiculo' => $id_vehiculo,
                'tarifa' => $tarifa,
                'id_viaje' => $id_viaje
            ]);

            // Actualizamos el estado del conductor a ocupado
            $pdo->prepare("UPDATE conductores SET estado_conductor = 'ocupado' WHERE id_conductor = ?")->execute([$id_conductor]);
            
            // Actualizamos el estado del vehículo a ocupado
            $pdo->prepare("UPDATE vehiculos SET estado_vehiculo = 'ocupado' WHERE id_vehiculo = ?")->execute([$id_vehiculo]);

            header("Location: menu.php?msg=viaje_asignado");
            exit;
        } else {
            header("Location: menu.php?msg=no_hay_pendientes");
            exit;
        }

    } catch (PDOException $e) {
        die("Error al asignar el viaje: " . $e->getMessage());
    }
}
?>