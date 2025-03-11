<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$pdo = conectarDB();


try {
    $stmt = $pdo->prepare("
        SELECT f.* 
        FROM Feedback f
        WHERE f.idJugador = :idUsuario
    ");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener feedback: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Feedback</title>
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
    <div class="content" id="feedback">
        <section class="section">
            <h2>Feedback del Coach</h2>
            <?php if (count($feedbacks) > 0): ?>
                <ul>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <li>
                            <strong>Comentario:</strong> <?php echo htmlspecialchars($feedback["comentario"]); ?> -
                            <strong>Calificación:</strong> <?php echo htmlspecialchars($feedback["calificacion"]); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No hay feedback disponible.</p>
            <?php endif; ?>
        </section>
    </div>
    <footer>
        <p>&copy; 2025 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>