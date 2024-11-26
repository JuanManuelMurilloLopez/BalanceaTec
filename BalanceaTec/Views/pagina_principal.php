<?php

session_start();

//Verificar si el usuario está logueado
if (!isset($_SESSION['user_ID'])) {
    header("Location: ../../inicio_sesion.php"); //Redirigir a login si no está logueado
    exit;
}

//Recuperar el user_ID de la sesión
$user_ID = $_SESSION['user_ID'];
$username = $_SESSION['user_name'];

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

//Obtener dispositivos del usuario
$sql = "SELECT device_id, device_name FROM device WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

//Arreglo para guardar los dispositivos
$devices = [];
while( $row = $result->fetch_assoc() ){
    //Agregar los datos de la consulta a los dispositivos
    $devices[] = $row;
}
$stmt->close();
//Verificar si se ha enviado un dispositivo seleccionado
if(isset($_POST["seleccionar_dispositivo"])){
    $_SESSION['selected_device'] = $_POST['seleccionar_dispositivo'];
}

//Recuperar el dispositivo seleccionado de la sesión
$selected_device = isset($_SESSION['selected_device']) ? $_SESSION['selected_device'] : null;


//Obtener valores
//Temperatura
//Get the latest temperature value
$sql = "SELECT temperature_value FROM temperature WHERE device_id = ? ORDER BY temperature_id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_device);
$stmt->execute();

$result = $stmt->get_result();
//Fetch the latest temperature
$lastest_temperature = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_temperature = $row['temperature_value'] . "ºC";
} else {
    $lastest_temperature = "No data";
}
$stmt->close();
//Humedad
//Get
$sql = "SELECT humidity_value FROM humidity WHERE device_id = ? ORDER BY humidity_id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_device);
$stmt->execute();
$result = $stmt->get_result();
//Fetch
$lastest_humidity = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_humidity = $row['humidity_value'] . "ºC";
} else {
    $lastest_humidity = "No data";
}
$stmt->close();

//Inclinación
//Get
$sql = "SELECT in_x FROM rotation WHERE device_id = ? ORDER BY rotation_ID DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_device);
$stmt->execute();
$result = $stmt->get_result();
//Fetch
$lastest_rot_x = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_rot_x = $row['in_x'] . "º";
} else {
    $lastest_rot_x = "No data";
}
$stmt->close();

$sql = "SELECT in_y FROM rotation WHERE device_id = ? ORDER BY rotation_ID DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_device);
$stmt->execute();
$result = $stmt->get_result();
//Fetch
$lastest_rot_y = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_rot_y = $row['in_y'] . "º";
} else {
    $lastest_rot_y = "No data";
}
$stmt->close();

$sql = "SELECT in_z FROM rotation WHERE device_id = ? ORDER BY rotation_ID DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_device);
$stmt->execute();
$result = $stmt->get_result();
//Fetch
$lastest_rot_z = '';
if( $result->num_rows > 0){
    $row = $result->fetch_assoc();
    $lastest_rot_z = $row['in_z'] . "º";
} else {
    $lastest_rot_z = "No data";
}
$stmt->close();

//Cuando el usuario presione el botón de "Guardar Cambios", actualiza la base de datos
if (isset($_POST["SAVE"])) {
    //Crear consulta
    $sql = "INSERT INTO limit_value (min_temperature, max_temperature, min_humidity, max_humidity, aceleration_tolerance, rotation_tolerance, device_ID) VALUES (?, ?, ?, ?, ?, ?, ?)";
    //Preparar consulta
    $stmt = $conn->prepare($sql);
    //Añadir los valores a la consulta
    $stmt->bind_param("dddddds", $_POST["inpTEMP_MIN"], $_POST["inpTEMP_MAX"], $_POST["inpHUM_MIN"], $_POST["inpHUM_MAX"], $_POST["inpTOL"], $_POST["inpACE"], $selected_device);
    
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
    <title>BalanceaTec | Página Principal</title>
    <link rel="stylesheet" href="../Style/pagina_principal.css">
</head>
<body>
    <header>
        <button onclick="window.location.href = 'configuracion_cuenta.php'">Ajustes de cuenta</button>
        <button onclick="window.location.href = '../../inicio_sesion.php'" >Log out</button>
    </header>
    <div id="Titulo">
        <h1>BalanceaTec</h1>
    </div>
    <div id="Descripcion">
        <p>BalanceaTec optimiza el transporte de sustancias, garantizando condiciones ideales durante todo el proceso.</p>
        <p class="slogan">"Porque en el transporte, cada detalle importa."</p>
    </div>
    
    <div id="Seleccion_Dispositivo">
        <form method="post" action="">
            <label for="seleccionar_dispositivo">Selecciona el Dispositivo: </label>
            <select name="seleccionar_dispositivo" id="seleccionar_dispositivo" onchange="this.form.submit()">
                <?php if (!empty($devices)): ?>
                    <?php foreach ($devices as $device): ?>
                        <option value="<?php echo htmlspecialchars($device['device_id']); ?>" 
                        <?php echo ($device['device_id'] == $selected_device) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($device['device_name']); ?>
                    </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No hay dispositivos disponibles</option>
                <?php endif; ?>
            </select>
        </form>
    
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
                <div>
                    <p>X: <?php echo $lastest_rot_x; ?></p>
                    <p>Y: <?php echo $lastest_rot_y; ?></p>
                    <p>Z: <?php echo $lastest_rot_z; ?></p>
                </div>
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
