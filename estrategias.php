<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$pdo = conectarDB();


try {
    $stmt = $pdo->prepare("
        SELECT e.* 
        FROM Estrategia e
        JOIN Estrategia_Equipo ee ON e.idEstrategia = ee.idEstrategia
        JOIN Usuario_Equipo ue ON ee.idEquipo = ue.idEquipo
        WHERE ue.idUsuario = :idUsuario
    ");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $estrategias = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener estrategias: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estrategias</title>
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
    <div class="content" id="estrategias">
        <section class="section">
            <h2>Estrategias de Equipo</h2>
            <?php if (count($estrategias) > 0): ?>
                <ul>
                    <?php foreach ($estrategias as $estrategia): ?>
                        <li>
                            <strong>Descripción:</strong> <?php echo htmlspecialchars($estrategia["descripcion"]); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay estrategias registradas.</p>
            <?php endif; ?>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>