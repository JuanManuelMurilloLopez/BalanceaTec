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

if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST["Aceptar"])){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if($password != $confirm_password){
        $message = "Las contraseñas no coinciden.";
    }
    else{
        //Si el nombre de usuario ya existe
        $query_check = "SELECT * FROM user_account WHERE user_name = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0){
            $message = "Este nombre de usuaio ya existe.";
        }
        else{
            // Insertar los datos en la tabla user_account
            // Para encriptar la contraseña -> $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Cifrar la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query_insert = "INSERT INTO user_account (user_name, password) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($query_insert);
            $stmt_insert->bind_param("ss", $username, $hashed_password);

            if ($stmt_insert->execute()) {
                $message = "Cuenta creada exitosamente.";
                //Redirigir al usuario a la página de inicio de sesión
                header("Location: ../../inicio_sesion.php");
                exit;
            } else {
                $message = "Error al crear la cuenta. Por favor, inténtalo de nuevo.";
            }
            //Cerrar las declaraciones
            $stmt_check->close();
            $stmt_insert->close();
        }
    }


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BalanceaTec | Crear Cuenta</title>
    <link rel="stylesheet" href="..\Style\Inicio_sesion.css">
    <link rel="stylesheet" href="crear_cuenta.css">
</head>
<body>
    <div class="login">
        <div id="Titulo">
            <h1>BalanceaTec</h1>
        </div>
        <div class="login-screen">
            <div class="app-title">
                <h2>Crea tu cuenta</h2>
            </div>

            <?php if (!empty($message)): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="control-group">
                    <input type="text" class="login-field" name="username" placeholder="Usuario" id="login-name" required>
                </div><br>

                <div class="control-group">
                    <input type="password" class="login-field" name="password" placeholder="Contraseña" id="login-pass" required>
                </div><br>

                <div class="control-group">
                    <input type="password" class="login-field" name="confirm_password" placeholder="Confirmar Contraseña" id="confirm-pass" required>
                </div><br>

                <button type="submit" class="btn btn-primary btn-large btn-block" name="Aceptar">Aceptar</button>
            </form>
        </div>
    </div>
</body>
</html>
