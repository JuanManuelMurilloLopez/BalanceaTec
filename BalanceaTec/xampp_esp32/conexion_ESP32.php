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

//Recibir datos del ESP
$device_ID = $_POST['device_ID'];
$temperature = $_POST['temperature'];
$humidity = $_POST['humidity'];
$rotation_x = $_POST['rotation_x'];
$rotation_y = $_POST['rotation_y'];
$rotation_z = $_POST['rotation_z'];
$acceleration_x = $_POST['acceleration_x'];
$acceleration_y = $_POST['acceleration_y'];
$acceleration_z = $_POST['acceleration_z'];

/* CONSULTAS */

//Temperatura
$sql_temp = "INSERT INTO temperature (temperature_value, device_ID) VALUES ('$temperature', '$device_ID')";
$conn->query($sql_temp);

//Humedad
$sql_hum = "INSERT INTO humidity (humidity_value, device_ID) VALUES ('$humidity', '$device_ID')";
$conn->query($sql_hum);

//Inclinación
$sql_inc = "INSERT INTO rotation (in_x, in_y, in_z, device_ID) VALUES ('$rotation_x', '$rotation_y', '$rotation_z', '$device_ID')";
$conn->query($sql_inc);

//Aceleración
$sql_ac = "INSERT INTO aceleration (in_x, in_y, in_z, device_ID) VALUES ('$acceleration_x', '$acceleration_y', '$acceleration_z', '$device_ID')";
$conn->query($sql_ac);

if($conn->affected_rows > 0){
    echo "Datos guardados correctamente.";
}
else{
    echo "Error al guardar los datos";
}

$conn->close();

?>
