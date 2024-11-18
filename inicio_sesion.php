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

//Cuando el usuario presione el botón de "Entrar", se manda la consulta a la base de datos
if (isset($_POST["Entrar"])) {
    //Crear consulta
    //$sql = "INSERT INTO limit_value (min_temperature, max_temperature, min_humidity, max_humidity, aceleration_tolerance, rotation_tolerance) VALUES (?, ?, ?, ?, ?, ?)";
    $sql = "SELECT user_ID FROM user_account WHERE user_name = '' AND password = ''";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec | Inicia Sesión</title>
    <link rel="stylesheet" href="\BalanceaTec\Style\Inicio_sesion.css">
</head>
<body>
    <div id="Titulo">
        <h1>BalanceaTec</h1>
    </div>
    <div class="login">
        <div class="login-screen">
            <div class="app-title">
                <h1>Bienvenido</h1>
            </div>

            <form action="login.php" method="POST" class="login-form">
                <div class="control-group">
                    <input type="text" class="login-field" name="username" placeholder="usuario" id="login-name" required>
                    <label class="login-field-icon fui-user" for="login-name"></label>
                </div>

                <div class="control-group">
                    <input type="password" class="login-field" name="password" placeholder="contraseña" id="login-pass" required>
                    <label class="login-field-icon fui-lock" for="login-pass"></label>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-block" name="Entrar">Entrar</button>
                <a class="login-link" href="#">¿Olvidaste tu contraseña?</a>
            </form>
        </div>
    </div>
</body>
</html>
