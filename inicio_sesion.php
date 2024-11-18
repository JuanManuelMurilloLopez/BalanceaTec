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

//Inicializar mensaje vacío
$message = "";

//Cuando el usuario presione el botón de "Entrar", se manda la consulta a la base de datos
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["Entrar"])) {
    //Guardar datos de entrada del usuario
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    if(empty($username) || empty($password)) {
        $message = "Usuario o Contraseña vacíos";
    }
    //Consulta a la base
    $query = "SELECT * FROM user_account WHERE user_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    //Verificar si el usuario existe
    if($result->num_rows > 0){
        $system_user = $result->fetch_assoc();
        //Para verificar una contraseña cifrada -> password_verify($password, $system_user["password"])
        if($password == $system_user["password"]){
            echo "Sesión iniciada";
            //Sesión iniciada con éxito
            session_start();
            $_SESSION['user_ID'] = $system_user["user_ID"];
            $_SESSION['user_name'] = $system_user["user_name"];
            header("Location: conexion_BD.php");
            exit;
        }
        else{
            $message = "Usuario o Contraseña incorrecto.";
        }
    }
    else{
        $message = "Usuario o Contraseña incorrecto.";
    }
    // Redirigir para evitar el resubmit
    if (!empty($message)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=" . urlencode($message));
        exit;
    }
}
// Mostrar mensaje de error desde GET (opcional)
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
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

            <?php if (!empty($message)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="control-group">
                    <input type="text" class="login-field" name="username" placeholder="usuario" id="login-name" required>
                    <label class="login-field-icon fui-user" for="login-name"></label>
                </div>

                <div class="control-group">
                    <input type="password" class="login-field" name="password" placeholder="contraseña" id="login-pass" required>
                    <label class="login-field-icon fui-lock" for="login-pass"></label>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-block" name="Entrar">Entrar</button>
                <a class="login-link" href="\BalanceaTec\Views\crear_cuenta.php">Crear cuenta</a>
                <a class="login-link" href="\BalanceaTec\Views\contrasena_olvidada.php">¿Olvidaste tu contraseña?</a>
            </form>
        </div>
    </div>
</body>
</html>
