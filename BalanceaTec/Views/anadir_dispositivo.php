<?php
session_start();

//Verificar si el usuario está logueado
if (!isset($_SESSION['user_ID'])) {
    header("Location: ../../inicio_sesion.php"); //Redirigir a login si no está logueado
    exit;
}

//Recuperar el user_ID de la sesión
$user_ID = $_SESSION['user_ID'];

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

//Inicializar mensaje vacío
$message = "";

if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["Guardar"])){
    $D_Name = trim($_POST['Device_Name']);
    $MAC_A = trim($_POST['MAC_Adress']);

        //Si el dispositivo ya existe
        $query_check = "SELECT * FROM device WHERE device_ID = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("s", $D_Name);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0){
            $message = "Este dispositivo ya existe.";
        }
        else{
            // Insertar los datos en la tabla device
            $query_insert = "INSERT INTO device (device_ID, device_name, user_ID) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("sss", $MAC_A, $D_Name, $user_ID);

            if ($stmt_insert->execute()) {
                $message = "Dispositivo añadido exitosamente.";
            } else {
                $message = "Error al añadir dispositivo. Por favor, inténtalo de nuevo.";
            }
            //Cerrar las declaraciones
            $stmt_check->close();
            $stmt_insert->close();
        }


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec | Añadir Dispositivo</title>
    <link rel="stylesheet" href="anadir_dispositivo.css"> 
</head>
<body>
    <div class="login">
        <div id="Titulo">
            <h1>BalanceaTec</h1>
        </div>

        <div class="login-screen">
            <div class="app-title">
                <h2>Añade tu nuevo dispositivo</h2>
            </div>

            <?php if (!empty($message)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="control-group">
                    <input type="text" class="login-field" name="Device_Name" placeholder="Nombre del dispositivo" id="Device_Name" required>
                </div><br>

                <div class="control-group">
                    <input type="text" class="login-field" name="MAC_Adress" placeholder="Dirección MAC" id="MAC_Adress" required>
                </div><br>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" name="Guardar">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='configuracion_cuenta.php'">Regresar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
