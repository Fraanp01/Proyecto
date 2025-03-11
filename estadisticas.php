<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$pdo = conectarDB();


try {
    $stmt = $pdo->prepare("SELECT SUM(kills) as totalKills, SUM(muertes) as totalMuertes FROM Estadisticas WHERE idUsuario = :idUsuario");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $estadisticas = $stmt->fetch();
} catch (PDOException $e) {
    die("Error al obtener estadísticas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="CStats Logo" />
        </div>
        <nav class="navigation">
            <a href="dashboard.php">Dashboard</a>
            <a href="partidas.php">Partidas</a>
            <a href="estadisticas.php">Estadísticas</a>
            <a href="estrategias.php">Estrategias</a>
            <a href="feedback.php">Feedback</a>
        </nav>
    </header>
    <div class="content" id="estadisticas">
        <section class="section">
            <h2>Estadísticas Generales</h2>
            <?php if ($estadisticas): ?>
                <div class="stats">
                    <div class="stat">
                        <h3>K/D Ratio</h3>
                        <p>
                            <?php 
                            if ($estadisticas["totalMuertes"] > 0) {
                                echo round($estadisticas["totalKills"] / $estadisticas["totalMuertes"], 2);
                            } else {
                                echo "N/A";
                            }
                            ?>
                        </p>
                    </div>
                    <div class="stat">
                        <h3>Victorias</h3>
                        <p>120</p> 
                    </div>
                    <div class="stat">
                        <h3>Derrotas</h3>
                        <p>85</p>
                    </div>
                </div>
            <?php else: ?>
                <p>No hay estadísticas disponibles.</p>
            <?php endif; ?>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>