<?php

include "config.php";


$pdo = conectarDB();


$stmt = $pdo->query("SELECT user, pass, idUsuario FROM login");
$usuarios = $stmt->fetchAll();


foreach ($usuarios as $usuario) {
    $user = $usuario["user"];
    $pass = $usuario["pass"]; 
    $idUsuario = $usuario["idUsuario"];

    
    $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

    
    $stmt = $pdo->prepare("UPDATE login SET pass = :hashedPass WHERE idUsuario = :idUsuario");
    $stmt->execute(["hashedPass" => $hashedPass, "idUsuario" => $idUsuario]);

    echo "Contraseña actualizada para el usuario: $user<br>";
}

echo "¡Todas las contraseñas han sido hasheadas correctamente!";
?>