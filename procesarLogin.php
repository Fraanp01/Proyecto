<?php
session_start();
include "config.php"; 

function consultaPass($user, $pass) {
    $pdo = conectarDB();
    if (!$pdo) {
        return "ERROR: No se pudo conectar a la base de datos.";
    }

    $consulta = "SELECT * FROM login WHERE user = :param";
    $resul = $pdo->prepare($consulta);

    if (!$resul) {
        return "ERROR: No se pudo preparar la consulta.";
    }

    $resul->execute(["param" => $user]);
    $registro = $resul->fetch();

    if (!$registro) {
        return "ERROR: Usuario no encontrado.";
    }

    if ($pass !== $registro["pass"]) {
        return "ERROR: Contraseña incorrecta.";
    }

    $_SESSION["username"] = $user;
    $_SESSION["idUsuario"] = $registro["idUsuario"];
    $_SESSION["usuario_id"] = $registro["idUsuario"]; 
    
    if (isset($registro["role"])) {
        $_SESSION["role"] = $registro["role"];
    } else {
        $_SESSION["role"] = "usuario";
    }
    
    if (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']); 
        header("Location: $redirect_url");
        exit();
    } else {
        header("Location: principal.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["user"];
    $pass = $_POST["pass"];
    $mensaje = consultaPass($user, $pass);
    echo $mensaje; 
}
?>