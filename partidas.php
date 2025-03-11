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
    
    $stmt = $pdo->prepare("SELECT * FROM Partida WHERE idUsuario = :idUsuario ORDER BY fecha DESC");
    $stmt->execute(["idUsuario" => $idUsuario]);
    $partidas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener las partidas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Partidas</title>
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

</header>

<main class="content">
    <h1>Mis Partidas</h1>

    
    <div class="button-group">
        <a href="registrar_partida.php" class="btn">Registrar Nueva Partida</a>
    </div>

    
    <?php if (count($partidas) > 0): ?>
        <div class="table-container">
            <table class="partidas-table">
                <thead>
                    <tr>
                        <th>Partida</th>
                        <th>Fecha</th>
                        <th>Mapa</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partidas as $partida): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($partida["idPartida"]); ?></td>
                            <td><?php echo htmlspecialchars($partida["fecha"]); ?></td>
                            <td><?php echo htmlspecialchars($partida["mapa"]); ?></td>
                            <td><?php echo htmlspecialchars($partida["resultado"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No tienes partidas registradas aún.</p>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2025 Tu Proyecto. Todos los derechos reservados.</p>
</footer>

</body>
</html>
