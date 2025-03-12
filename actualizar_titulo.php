<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";
$pdo = conectarDB();

// Verificar si el usuario es coach
$esCoach = isset($_SESSION["role"]) && $_SESSION["role"] === 'coach';

if (!$esCoach) {
    header("Location: principal.php");
    exit();
}

// Verificar si se proporcionó un ID de partida y un nuevo título
if (!isset($_POST["idPartida"]) || !isset($_POST["nuevo_titulo"])) {
    header("Location: partidas.php");
    exit();
}

$idPartida = $_POST["idPartida"];
$nuevoTitulo = trim($_POST["nuevo_titulo"]);

// Validar que el título no esté vacío
if (empty($nuevoTitulo)) {
    header("Location: ver_partida.php?id=" . $idPartida . "&error=titulo_vacio");
    exit();
}

try {
    // Actualizar el título en la base de datos
    $stmt = $pdo->prepare("UPDATE Partida SET titulo = ? WHERE idPartida = ?");
    $stmt->execute([$nuevoTitulo, $idPartida]);
    
    // Redirigir de vuelta a la página de la partida con un mensaje de éxito
    header("Location: ver_partida.php?id=" . $idPartida . "&mensaje=titulo_actualizado");
    exit();
} catch (PDOException $e) {
    // En caso de error, redirigir con mensaje de error
    header("Location: ver_partida.php?id=" . $idPartida . "&error=" . urlencode("Error al actualizar: " . $e->getMessage()));
    exit();
}
?>