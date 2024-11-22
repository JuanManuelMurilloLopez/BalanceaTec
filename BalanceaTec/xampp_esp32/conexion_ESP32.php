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
$aceleration_x = $_POST['aceleration_x'];
$aceleration_y = $_POST['aceleration_y'];
$aceleration_z = $_POST['aceleration_z'];

/* CONSULTAS */

//Temperatura
$sql_temp = "INSERT INTO temperature (temperature_value, device_ID) VALUES (?, ?)";
$stmt_temp = $conn->prepare($sql_temp);
$stmt_temp->bind_param("ds", $temperature, $device_ID);
if ($stmt_temp->execute()) {
    echo "Datos insertados en Temperatura.";
} else {
    echo "Los datos no fueron insertados.";
}
//Cerrar la delcaración
$stmt_temp->close();

//Humedad
$sql_hum = "INSERT INTO humidity (humidity_value, device_ID) VALUES (?, ?)";
$stmt_hum = $conn->prepare($sql_hum);
$stmt_hum->bind_param("ds", $humidity, $device_ID);
if ($stmt_hum->execute()) {
    echo "Datos insertados en Humedad.";
} else {
    echo "Los datos no fueron insertados.";
}
//Cerrar la declaración
$stmt_hum->close();

//Inclinación
$sql_inc = "INSERT INTO rotation (in_x, in_y, in_z, device_ID) VALUES (?, ?, ?, ?)";
$stmt_inc = $conn->prepare($sql_inc);
$stmt_inc->bind_param("ddds", $rotation_x, $rotation_y, $rotation_z, $device_ID);
if ($stmt_inc->execute()) {
    echo "Datos insertados en Rotación";
} else {
    echo "Los datos no fueron insertados.";
}
//Cerrar la declaración
$stmt_inc->close();

//Aceleración
$sql_ac = "INSERT INTO aceleration (in_x, in_y, in_z, device_ID) VALUES (?, ?, ?, ?)";
$stmt_ac = $conn->prepare($sql_ac);
$stmt_ac->bind_param("ddds", $aceleration_x, $aceleration_y, $aceleration_z, $device_ID);
if ($stmt_ac->execute()) {
    echo "Datos insertados en Aceleración";
} else {
    echo "Los datos no fueron insertados.";
}
//Cerrar la declaración
$stmt_ac->close();

?>
