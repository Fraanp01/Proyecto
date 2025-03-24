<?php
session_start();
include "config.php";

function guardarDatos($user, $pass) {
    $pdo = conectarDB();
    if ($pdo != null) {
        $consultaExistencia = "SELECT * FROM login WHERE user = :paramUser ";
        $resulExistencia = $pdo->prepare($consultaExistencia);
        $resulExistencia->execute(["paramUser " => $user]);
        $registroExistente = $resulExistencia->fetch();

        if ($registroExistente) {
            $_SESSION["error_registro"] = "El usuario '$user' ya existe.";
            return false;
        }

        $consulta = "INSERT INTO login (user, pass) VALUES (:paramUser , :paramPass)";
        $resul = $pdo->prepare($consulta);
        if ($resul->execute(["paramUser " => $user, "paramPass" => $pass])) {
            $_SESSION["username"] = $user;
            header("Location: welcome.php");
            exit();
        } else {
            $_SESSION["error_registro"] = "ERROR: Registro NO insertado.";
            return false;
        }
    } else {
        $_SESSION["error_registro"] = "ERROR: No se pudo conectar a la base de datos.";
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST["user"]);
    $pass = trim($_POST["pass"]);

    if (empty($user) || empty($pass)) {
        $_SESSION["error_registro"] = "Usuario y contraseña son obligatorios.";
    } else {
        guardarDatos($user, $pass);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CStats</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="login-container">
        <?php if (isset($_SESSION["error_registro"])): ?>
            <div class="error-message">
                <?php 
                    echo $_SESSION["error_registro"];
                    unset($_SESSION["error_registro"]);
                ?>
            </div>
        <?php endif; ?>
        
        <h1>Registrarse</h1>
        <form method="POST" action="altaLogin.php">
            <div class="input-group">
                <label for="user">Usuario</label>
                <input type="text" name="user" id="user" required>
            </div>
            <div class="input-group">
                <label for="pass">Contraseña</label>
                <input type="password" name="pass" id="pass" required>
            </div>
            <button type="submit" class="btn register-btn">Crear Cuenta</button>
        </form>
    </div>
</body>
</html>