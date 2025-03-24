<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";
$pdo = conectarDB();

$esCoach = isset($_SESSION["role"]) && $_SESSION["role"] === 'coach';

if (!$esCoach) {
    header("Location: principal.php");
    exit();
}

if (!isset($_POST["idPartida"]) || !isset($_POST["nuevo_titulo"])) {
    header("Location: partidas.php");
    exit();
}

$idPartida = $_POST["idPartida"];
$nuevoTitulo = trim($_POST["nuevo_titulo"]);

if (empty($nuevoTitulo)) {
    header("Location: ver_partida.php?id=" . $idPartida . "&error=titulo_vacio");
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE Partida SET titulo = ? WHERE idPartida = ?");
    $stmt->execute([$nuevoTitulo, $idPartida]);
    
    header("Location: ver_partida.php?id=" . $idPartida . "&mensaje=titulo_actualizado");
    exit();
} catch (PDOException $e) {
    header("Location: ver_partida.php?id=" . $idPartida . "&error=" . urlencode("Error al actualizar: " . $e->getMessage()));
    exit();
}
?>