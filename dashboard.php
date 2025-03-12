Aquí tienes el archivo dashboard.php con la sección de feedback eliminada:

```php
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
    // Obtener última partida
    $stmt = $pdo->prepare("
        SELECT * 
        FROM Partida 
        WHERE idUsuario = :idUsuario
        ORDER BY fecha DESC
        LIMIT 1
    ");
    $stmt->execute(["idUsuario" => $idUsuario]);
    $ultimaPartida = $stmt->fetch();
    
    // Obtener todas las partidas para estadísticas
    $stmtTotal = $pdo->prepare("SELECT * FROM Partida WHERE idUsuario = :idUsuario");
    $stmtTotal->execute(["idUsuario" => $idUsuario]);
    $partidas = $stmtTotal->fetchAll();

    $totalPartidas = count($partidas);
    $victorias = array_filter($partidas, fn($partida) => $partida['resultado'] == 'Victoria');
    $contadorVictorias = count($victorias);
    $derrotas = $totalPartidas - $contadorVictorias;
    
    // Obtener estadísticas adicionales como K/D ratio
    $stmtStats = $pdo->prepare("
        SELECT 
            SUM(kills) as totalKills, 
            SUM(muertes) as totalMuertes,
            AVG(kills) as avgKills
        FROM Estadisticas 
        WHERE idUsuario = :idUsuario
    ");
    $stmtStats->execute(["idUsuario" => $idUsuario]);
    $estadisticas = $stmtStats->fetch();
    
    $kdRatio = 0;
    if ($estadisticas && $estadisticas["totalMuertes"] > 0) {
        $kdRatio = round($estadisticas["totalKills"] / $estadisticas["totalMuertes"], 2);
    }
    
    // Obtener mapas más jugados
    $stmtMapas = $pdo->prepare("
        SELECT mapa, COUNT(*) as total 
        FROM Partida 
        WHERE idUsuario = :idUsuario 
        GROUP BY mapa 
        ORDER BY total DESC 
        LIMIT 5
    ");
    $stmtMapas->execute(["idUsuario" => $idUsuario]);
    $mapasPopulares = $stmtMapas->fetchAll();

    // Comprobar logros
    $logros = [
        'primeras_10_victorias' => $contadorVictorias >= 10,
        'racha_5_victorias' => false
    ];

    // Verificar racha de 5 victorias consecutivas
    $rachaActual = 0;
    $rachaMaxima = 0;
    
    foreach ($partidas as $partida) {
        if ($partida['resultado'] == 'Victoria') {
            $rachaActual++;
            $rachaMaxima = max($rachaMaxima, $rachaActual);
        } else {
            $rachaActual = 0;
        }
    }
    
    $logros['racha_5_victorias'] = $rachaMaxima >= 5;

} catch (PDOException $e) {
    $error = "Error al obtener los datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CS2 Stats Tracker</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel=" stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <div class="logo">
        <img src="img/logo-removebg-preview.png" alt="CStats Logo">
    </div>
    <nav class="navigation">
        <a href="principal.php"><i class="fas fa-home"></i> Inicio</a>
        <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
        <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </nav>
</header>

<div class="content">
    <h1>Bienvenido al Dashboard</h1>
    <div class="features-summary">
        <div class="feature-card">
            <h3>Última Partida</h3>
            <p>Mapa: <?php echo $ultimaPartida['mapa']; ?></p>
            <p>Resultado: <?php echo $ultimaPartida['resultado']; ?></p>
            <p>Fecha: <?php echo $ultimaPartida['fecha']; ?></p>
        </div>
        <div class="feature-card">
            <h3>Estadísticas Generales</h3>
            <p>Total de Partidas: <?php echo $totalPartidas; ?></p>
            <p>Victorias: <?php echo $contadorVictorias; ?></p>
            <p>Derrotas: <?php echo $derrotas; ?></p>
            <p>K/D Ratio: <?php echo $kdRatio; ?></p>
        </div>
        <div class="feature-card">
            <h3>Mapas Más Jugados</h3>
            <ul>
                <?php foreach ($mapasPopulares as $mapa): ?>
                    <li><?php echo $mapa['mapa']; ?>: <?php echo $mapa['total']; ?> partidas</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="feature-card">
        <h3>Gráfico de Estadísticas</h3>
        <canvas id="statsChart" width="400" height="200"></canvas>
        <script>
            const ctx = document.getElementById('statsChart').getContext('2d');
            const statsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Victorias', 'Derrotas'],
                    datasets: [{
                        label: 'Partidas',
                        data: [<?php echo $contadorVictorias; ?>, <?php echo $derrotas; ?>],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 99, 132, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    </div>

    <div class="feature-card">
        <h3>Logros</h3>
        <ul>
            <li>Primeras 10 victorias: <?php echo $logros['primeras_10_victorias'] ? '✔️' : '❌'; ?></li>
            <li>Racha de 5 victorias: <?php echo $logros['racha_5_victorias'] ? '✔️' : '❌'; ?></li>
        </ul>
    </div>


    <div class="feature-card">
        <h3>Notificaciones</h3>
        <ul>
            <li>No tienes nuevas notificaciones.</li>
            <!-- Aquí puedes agregar lógica para mostrar notificaciones dinámicamente -->
        </ul>
    </div>
</div>

<footer>
    <p>&copy; 2023 CS2 Stats Tracker. Todos los derechos reservados.</p>
</footer>

</body>
</html>