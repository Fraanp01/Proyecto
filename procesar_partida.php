<?php

session_start();


if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}


require_once "config.php";


$pdo = conectarDB();


$mapa = $_POST["mapa"];
$resultado = $_POST["resultado"];
$fecha = $_POST["fecha"];


if (!isset($_SESSION["idUsuario"])) {
    die("Error: ID de usuario no encontrado en la sesión.");
}
$idUsuario = $_SESSION["idUsuario"];


try {
    $stmt = $pdo->prepare("INSERT INTO Partida (mapa, resultado, fecha, idUsuario) VALUES (:mapa, :resultado, :fecha, :idUsuario)");
    $stmt->execute([
        "mapa" => $mapa,
        "resultado" => $resultado,
        "fecha" => $fecha,
        "idUsuario" => $idUsuario
    ]);

    
    $idPartida = $pdo->lastInsertId();

    
    $stmt = $pdo->prepare("INSERT INTO Estadisticas (idUsuario, idPartida, kills, muertes) VALUES (:idUsuario, :idPartida, 0, 0)");
    $stmt->execute([
        "idUsuario" => $idUsuario,
        "idPartida" => $idPartida
    ]);

    
    header("Location: partidas.php");
    exit();
} catch (PDOException $e) {
    die("Error al registrar la partida: " . $e->getMessage());
}
?>