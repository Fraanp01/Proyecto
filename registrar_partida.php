<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";
$pdo = conectarDB();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mapa = $_POST["mapa"];
    $resultado = $_POST["resultado"];
    $fecha = $_POST["fecha"];
    $idUsuario = $_SESSION["idUsuario"];

    try {
        $stmt = $pdo->prepare("INSERT INTO Partida (mapa, resultado, fecha, idUsuario) VALUES (:mapa, :resultado, :fecha, :idUsuario)");
        $stmt->execute([
            "mapa" => $mapa,
            "resultado" => $resultado,
            "fecha" => $fecha,
            "idUsuario" => $idUsuario
        ]);

        header("Location: partidas.php");
        exit();
    } catch (PDOException $e) {
        die("Error al registrar la partida: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Partida</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>
    <nav class="navigation">
        <a href="index.php">Inicio</a>
        <a href="dashboard.php">Dashboard</a> 
        <a href="estadisticas.php">Estadísticas</a>
    </nav>
</header>

<main class="content">
    <h1>Registrar Nueva Partida</h1>
    <form action="registrar_partida.php" method="POST">
        <label for="mapa">Mapa:</label>
        <input type="text" id="mapa" name="mapa" required>

        <label for="resultado">Resultado:</label>
        <input type="text" id="resultado" name="resultado" required>

        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>

        <button type="submit" class="btn">Registrar Partida</button>
    </form>
</main>

<footer>
    <p>&copy; 2025 Tu Proyecto. Todos los derechos reservados.</p>
</footer>

</body>
</html>