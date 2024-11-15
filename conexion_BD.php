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

//Obtener valores
//Temperatura
//Get the latest temperature value
$sql = "SELECT temperature_value FROM temperature ORDER BY temperature_id DESC LIMIT 1";
$result = $conn->query($sql);
//Fetch the latest temperature
$lastest_temperature = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_temperature = $row['temperature_value'] . "ºC";
} else {
    $lastest_temperature = "No data";
}
//Humedad
//Get
$sql = "SELECT humidity_value FROM humidity ORDER BY humidity_id DESC LIMIT 1";
$result = $conn->query($sql);
//Fetch
$lastest_humidity = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_humidity = $row['humidity_value'] . "ºC";
} else {
    $lastest_humidity = "No data";
}

//Cuando el usuario presione el botón de "Guardar Cambios", actualiza la base de datos
if (isset($_POST["SAVE"])) {
    //Crear consulta
    $sql = "INSERT INTO limit_value (min_temperature, max_temperature, min_humidity, max_humidity, aceleration_tolerance, rotation_tolerance) VALUES (?, ?, ?, ?, ?, ?)";
    //Preparar consulta
    $stmt = $conn->prepare($sql);
    //Añadir los valores a la consulta
    $stmt->bind_param("dddddd", $_POST["inpTEMP_MIN"], $_POST["inpTEMP_MAX"], $_POST["inpHUM_MIN"], $_POST["inpHUM_MAX"], $_POST["inpTOL"], $_POST["inpACE"]);
    
    //Ejecutar consulta
    if ($stmt->execute()) {
        echo "Datos guardados correctamente.";
    } else {
        echo "Error al guardar los datos: " . $stmt->error;
    }
    //Cerrar consulta
    $stmt->close();
    // Redirigir al usuario a la misma página para evitar reenvío de formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

}

//Cerrar Conexión a la Base de Datos 
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec</title>
    <link href="styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="Titulo">
        <h1>BalanceaTec</h1>
    </div>
    <div id="Descripcion">
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Atque enim cupiditate aperiam praesentium, cum labore a ab quae voluptatibus earum fugit tempora dolore! Fugit, dolorem mollitia voluptas corporis esse quis.</p>
    </div>
    <div id="Wrapper_Contenido">
        <div id="Temperatura">
            <h2>Temperatura</h2>
            <p id="temperature_read"><?php echo $lastest_temperature; ?></p>
        </div>
        <div id="Humedad">
            <h2>Humedad</h2>
            <p id="humidity_read"><?php echo $lastest_humidity; ?></p>
        </div>
        <div id="Giro">
            <h2>Inclinación</h2>
            <div id="Seleccion_Eje">
                <label for="seleccionar_eje">Selecciona el eje: </label>
                <select name="seleccionar_eje" id="seleccionar_eje">
                    <option value="X">X</option>
                    <option value="Y">Y</option>
                    <option value="Z">Z</option>
                </select>
            </div>
        </div>
    </div>
    <form method="post" action="">
        <div id="Wrapper_Limits">
            <div id="Temp">
                <h3>Temperatura Máxima</h3>
                <input type="number" name="inpTEMP_MAX" id="inpTEMP_MAX">
                <h3>Temperatura Mínima</h3>
                <input type="number" name="inpTEMP_MIN" id="inpTEMP_MIN">
            </div>
            <div id="Hum">
                <h3>Humedad Máxima</h3>
                <input type="number" name="inpHUM_MAX" id="inpHUM_MAX">
                <h3>Humedad Mínima</h3>
                <input type="number" name="inpHUM_MIN" id="inpHUM_MIN">
            </div>
            <div id="Inc">
                <h3>Tolerancia de rotación</h3>
                <input type="number" name="inpTOL" id="inpTOL">
            </div>
            <div id="Ace">
                <h3>Tolerancia de aceleración</h3>
                <input type="number" name="inpACE" id="inpACE">
            </div>
            <div>
                <button type="submit" name="SAVE">Guardar Cambios</button>
            </div>
        </div>
    </form>
</body>
</html>
