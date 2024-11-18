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
$password = $_SESSION['password'];

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

if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["Aceptar"])){

   //Obtener los datos del formulario
   $old_password = trim($_POST['old_password']);
   $new_password = trim($_POST['new_password']);
   $confirm_password = trim($_POST['confirm_password']);  

   //Verificar si las contraseñas coinciden
   if ($new_password !== $confirm_password) {
    echo "<p style='color:red;'>Las contraseñas no coinciden.</p>";
    }
    else{
        //Consultar la contraseña actual en la base de datos
        $query = "SELECT password FROM user_account WHERE user_ID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();

        //Verificar si la contraseña actual es correcta
        if ($user_data && password_verify($old_password, $user_data['password'])) {
            //Encriptar nueva contraseña
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            
            //Actualizar la contraseña en la base de datos
            $update_query = "UPDATE user_account SET password = ? WHERE user_ID = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_password_hashed, $user_ID);
            if ($update_stmt->execute()) {
                echo "<p style='color:green;'>Contraseña actualizada.</p>";
            } else {
                echo "<p style='color:red;'>Hubo un error al actualizar la contraseña.</p>";
            }
        } else {
            echo "<p style='color:red;'>La contraseña actual es incorrecta.</p>";
        }
    }
}

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec | Cambiar Contraseña</title>
    <link rel="stylesheet" href="..\Style\Inicio_sesion.css">
</head>
<body>
<div id="Titulo">
        <h1>BalanceaTec</h1>
    </div>
    <div class="login">
        <div class="login-screen">
            <div class="app-title">
                <h2>Ingresa tu nueva contraseña</h2>
            </div>

            <?php if (!empty($message)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="control-group">
                    <input type="text" class="login-field" name="username" placeholder="Contraseña Anterior" id="login-name" required>
                </div><br>

                <div class="control-group">
                    <input type="password" class="login-field" name="password" placeholder="Contraseña Nueva" id="login-pass" required>
                </div><br>

                <div class="control-group">
                    <input type="password" class="login-field" name="confirm_password" placeholder="Confirmar Contraseña Nueva" id="confirm-pass" required>
                </div><br>

                <button type="submit" class="btn btn-primary btn-large btn-block" name="Aceptar">Aceptar</button>
            </form>
        </div>
    </div>
</body>
</html>
