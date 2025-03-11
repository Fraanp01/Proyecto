<?php
session_start(); 


if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php"); 
    exit();
}


require_once "config.php"; 


$pdo = conectarDB();


$idUsuario = $_SESSION["idUsuario"];


try {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM Partida 
        WHERE idUsuario = :idUsuario
        ORDER BY fecha DESC
        LIMIT 1
    ");
    $stmt->execute(["idUsuario" => $idUsuario]);
    $ultimaPartida = $stmt->fetch();

    
    $stmtTotal = $pdo->prepare("SELECT * FROM Partida WHERE idUsuario = :idUsuario");
    $stmtTotal->execute(["idUsuario" => $idUsuario]);
    $partidas = $stmtTotal->fetchAll();

    $totalPartidas = count($partidas);
    $victorias = array_filter($partidas, fn($partida) => $partida['resultado'] == 'Victoria');
    $derrotas = $totalPartidas - count($victorias);

} catch (PDOException $e) {
    die("Error al obtener las partidas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
    <h1>Bienvenido a tu Dashboard, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>

    
    <section class="features-summary">
        <div class="feature-card">
            <h3>Última Partida</h3>
            <?php if ($ultimaPartida): ?>
                <p><strong>Mapa:</strong> <?php echo htmlspecialchars($ultimaPartida["mapa"]); ?></p>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars($ultimaPartida["fecha"]); ?></p>
                <p><strong>Resultado:</strong> <?php echo htmlspecialchars($ultimaPartida["resultado"]); ?></p>
            <?php else: ?>
                <p>Aún no has registrado ninguna partida.</p>
            <?php endif; ?>
        </div>

        <div class="feature-card">
            <h3>Estadísticas Rápidas</h3>
            <p><strong>Total de Partidas:</strong> <?php echo $totalPartidas; ?></p>
            <p><strong>Victorias:</strong> <?php echo count($victorias); ?></p>
            <p><strong>Derrotas:</strong> <?php echo $derrotas; ?></p>
        </div>

        <div class="feature-card">
            <h3>Feedback</h3>
            <p>¿Tienes algún comentario o sugerencia para mejorar el sistema? ¡Déjanos tu opinión!</p>
            <a href="feedback.php" class="btn">Deja tu Feedback</a>
        </div>
    </section>

</main>

<footer>
    <p>&copy; 2025 Tu Proyecto. Todos los derechos reservados.</p>
</footer>

</body>
</html>