<?php

//Credenciales para conectarse a la base
$host = 'localhost';
$user = 'reto';
$password = '';
$database = 'iot_reto';

//Abrir Conexión a la Base de Datos 
$conn = new mysqli($host, $user, $password, $database);

//Si la conexión falla...
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//Recibir el device_ID
$device_ID = $_GET['device_ID'];

//Obtener los límites de la base de datos
$sql = "SELECT max_temperature, min_temperature, max_humidity, min_humidity, rotation_tolerance, acceleration_tolerance 
        FROM limit_value 
        WHERE device_ID = '$device_ID'
        ORDER BY created_at DESC
        LIMIT 1";

$result = $conn->query($sql);

//Verificar si se encontraron datos
if ($result->num_rows > 0) {
    //Enviar los datos como JSON
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(["error" => "No se encontraron límites para este dispositivo"]);
}

//Cerrar conexión
$conn->close();

?>
