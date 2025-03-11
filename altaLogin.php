<?php
session_start();
include "config.php";

function guardarDatos($user, $pass) {
    $pdo = conectarDB();
    if ($pdo != null) {
        $consultaExistencia = "SELECT * FROM login WHERE user = :paramUser";
        $resulExistencia = $pdo->prepare($consultaExistencia);
        $resulExistencia->execute(["paramUser" => $user]);
        $registroExistente = $resulExistencia->fetch();

        if ($registroExistente) {
            echo "ERROR: El usuario '$user' ya existe.";
            return;
        }

        $consulta = "INSERT INTO login (user, pass) VALUES (:paramUser, :paramPass)";
        $resul = $pdo->prepare($consulta);
        if ($resul != null) {
            $hashedPass = password_hash($pass, PASSWORD_DEFAULT); // Hash de la contraseña
            if ($resul->execute(["paramUser" => $user, "paramPass" => $hashedPass])) {
                echo "Nuevo registro insertado <br>";
                $_SESSION["username"] = $user;
                header("Location: dashboard.php");
                exit();
            } else {
                echo "ERROR: Registro NO insertado <br>";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["user"];
    $pass = $_POST["pass"];
    guardarDatos($user, $pass);
}
?>