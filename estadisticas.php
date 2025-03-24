<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Incluir archivo de configuración de base de datos y SteamAPI
require_once("config.php");
require_once("SteamAPI.php");
$estadisticas = null; // Inicializa la variable

// Inicializar arrays para gráficos
$kdRatioData = [0, 0, 0, 0, 0];
$kdRatioLabels = ['Sin datos', 'Sin datos', 'Sin datos', 'Sin datos', 'Sin datos'];

try {
    // Establecer conexión a la base de datos
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $dbname = "bkwgpnt7d5hd7bpuiwbw";
    $usuario = "uq6vff78pyt2g5lo";
    $contrasena = "u6l50PObWFQEFcpTIp5a";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener estadísticas acumuladas del usuario
    $stmt = $pdo->prepare("SELECT 
                              SUM(kills) as totalKills, 
                              SUM(muertes) as totalMuertes, 
                              SUM(asistencias) as totalAsistencias,
                              SUM(headshots) as totalHeadshots,
                              SUM(mvps) as totalMvps
                           FROM Estadisticas 
                           WHERE idUsuario = :idUsuario");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $estadisticas = $stmt->fetch();
    
    // Si no hay estadísticas, establecer valores predeterminados
    if (!$estadisticas) {
        $estadisticas = [
            'totalKills' => 0,
            'totalMuertes' => 0,
            'totalAsistencias' => 0,
            'totalHeadshots' => 0,
            'totalMvps' => 0,
        ];
    }
    
    // Calcular KD ratio personal
    $userKdRatio = ($estadisticas['totalMuertes'] > 0) ? 
                    round($estadisticas['totalKills'] / $estadisticas['totalMuertes'], 2) : 
                    $estadisticas['totalKills'];
    
    // Obtener últimas 5 partidas para gráficos de evolución
    $stmt = $pdo->prepare("SELECT 
                              fecha, 
                              kills, 
                              muertes,
                              asistencias,
                              headshots,
                              mvps
                           FROM Estadisticas 
                           WHERE idUsuario = :idUsuario 
                           ORDER BY fecha DESC 
                           LIMIT 5");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $ultimasPartidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar datos para gráficos de evolución
    $partidas_kdRatioData = [];
    $fechasPartidas = [];
    $killsData = [];
    $muertesData = [];
    $hsData = [];
    $mvpsData = [];
    
    if (!empty($ultimasPartidas)) {
        foreach (array_reverse($ultimasPartidas) as $partida) {
            $partidaKdRatio = ($partida['muertes'] > 0) ? 
                            round($partida['kills'] / $partida['muertes'], 2) : 
                            $partida['kills'];
            $partidas_kdRatioData[] = $partidaKdRatio;
            $fechasPartidas[] = date('d-m-Y', strtotime($partida['fecha']));
            $killsData[] = $partida['kills'];
            $muertesData[] = $partida['muertes'];
            $hsData[] = $partida['headshots'];
            $mvpsData[] = $partida['mvps'];
        }
    }

} catch (PDOException $e) {
    error_log("Error en estadisticas.php: " . $e->getMessage());
    $error = "Error de conexión: " . $e->getMessage();
}

// Variables para la búsqueda de jugadores
$error = '';
$apiError = false;
$playerData = null;
$csgoStats = null;
$formattedStats = null;
$statsUrl = null;

// Procesar la búsqueda del jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id'])) {
    $steamId = trim($_POST['steam_id']);
    
    // Validación básica del Steam ID
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        // Inicializar SteamAPI
        $steamAPI = new SteamAPI();
        
        // Obtener datos del jugador
        $playerSummary = $steamAPI->getPlayerSummary($steamId);
        
        if (!$playerSummary) {
            $error = 'No se pudo encontrar información del jugador. Por favor, verifica el Steam ID.';
        } else {
            $playerData = $playerSummary;
            $statsUrl = "https://csstats.gg/player/{$steamId}";
            
            // Intentar obtener estadísticas de CS2
            $rawStats = $steamAPI->getCS2Stats($steamId);
            
            if ($rawStats && isset($rawStats['playerstats']['stats'])) {
                $csgoStats = $rawStats['playerstats']['stats'];
                
                // Inicializar estadísticas formateadas
                $formattedStats = [
                    'totalKills' => 0,
                    'totalDeaths' => 0,
                    'totalWins' => 0,
                    'totalRounds' => 0,
                    'winRate' => 0,
                    'headshots' => 0,
                    'assists' => 0,
                    'MVPs' => 0,
                ];
                
                foreach ($csgoStats as $stat) {
                    if ($stat['name'] === 'total_kills') {
                        $formattedStats['totalKills'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_deaths') {
                        $formattedStats['totalDeaths'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_wins') {
                        $formattedStats['totalWins'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_rounds') {
                        $formattedStats['totalRounds'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_headshots') {
                        $formattedStats['headshots'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_assists') {
                        $formattedStats['assists'] = $stat['value'];
                    } elseif ($stat['name'] === 'total_mvps') {
                        $formattedStats['MVPs'] = $stat['value'];
                    }
                }
                
                // Calcular tasa de victorias
                if ($formattedStats['totalRounds'] > 0) {
                    $formattedStats['winRate'] = round(($formattedStats['totalWins'] / $formattedStats['totalRounds']) * 100);
                }
                
                // Crear datos para el gráfico de KD ratio
                if ($formattedStats['totalDeaths'] > 0) {
                    $kdRatio = round($formattedStats['totalKills'] / $formattedStats['totalDeaths'], 2);
                } else {
                    $kdRatio = $formattedStats['totalKills'];
                }
                $kdRatioData = [$kdRatio, 1.0]; // Añadir el K/D global
                $kdRatioLabels = ['Tu K/D', 'K/D Global'];
            } else {
                $apiError = true;
            }
        }
    }
}

// Mostrar estadísticas en la interfaz
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de CS2</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family =Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1e1e1e;
            color: #f0f0f0;
        }
        .content {
            max-width: 800px;
            margin: 90px auto 20px;
            padding: 20px;
            background: #2c2c2c;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        h1, h2 {
            color: #e0e0e0;
        }
        .stat-box {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            margin: 10px 0;
            background: #3c3c3c;
            border-radius: 5px;
        }
        .stat-title {
            font-weight: bold;
        }
        .stat-value {
            font-size: 1.2em; 
            color: #1abc9c;
        }
        .error-message {
            color: #e74c3c;
        }
        .api-error {
            color: #f39c12;
        }
        .na-value {
            color: #999;
            font-style: italic;
            font-size: 0.9em;
        }
        .chart-container {
            margin: 30px 0;
        }
        .comparison-container {
            margin: 30px 0;
            padding: 15px;
            background: #262626;
            border-radius: 8px;
        }
        .stat-compare-box {
            margin: 15px 0;
        }
        .stat-bar-container {
            position: relative;
            height: 30px;
            width: 100%;
        }
        .stat-bar-global {
            position: absolute;
            height: 20px;
            background: #3498db;
            border-radius: 3px;
            text-align: center;
            font-size: 0.8em;
            line-height: 20px;
            color: white;
        }
        .stat-bar-user {
            position: absolute;
            height: 20px;
            background: #1abc9c;
            border-radius: 3px;
            text-align: center;
            font-size: 0.8em;
            line-height: 20px;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="nav-left">
                <div class="logo">
                    <img src="img/logo.png" alt="Logo">
                </div>
                <div class="navigation">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="partidas.php">Partidas</a>
                    <a href="estadisticas.php" class="active">Estadísticas</a>
                    <a href="perfil.php">Perfil</a>
                    <a href="feedback.php">Feedback</a>
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="content">
        <h1>Estadísticas de CS2</h1>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($apiError): ?>
            <p class="api-error">No se pudieron obtener estadísticas de CS2 para el jugador. Puede que el perfil sea privado o que no haya datos disponibles.</p>
        <?php endif; ?>
        
        <form method="POST">
            <label for="steam_id">Steam ID:</label>
            <input type="text" name="steam_id" id="steam_id" required>
            <button type="submit">Buscar</button>
        </form>
        
        <?php if ($playerData): ?>
            <h2>Información del Jugador</h2>
            <p> Nombre: <?php echo htmlspecialchars($playerData['personaname']); ?></p>
            <div class="stat-box">
                <span class="stat-title">Victorias:</span>
                <span class="stat-value"><?php echo $formattedStats['totalWins']; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-title">Muertes:</span>
                <span class="stat-value"><?php echo $formattedStats['totalDeaths']; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-title">K/D Ratio:</span>
                <span class="stat-value"><?php echo $userKdRatio; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-title">Headshots:</span>
                <span class="stat-value">
                    <?php 
                    if (isset($estadisticas['totalHeadshots']) && $estadisticas['totalHeadshots'] > 0) {
                        echo $estadisticas['totalHeadshots'];
                    } else {
                        echo '<span class="na-value">No disponible</span>';
                    }
                    ?>
                </span>
            </div>
            <div class="stat-box">
                <span class="stat-title">Asistencias:</span>
                <span class="stat-value">
                    <?php 
                    if (isset($estadisticas['totalAsistencias']) && $estadisticas['totalAsistencias'] > 0) {
                        echo $estadisticas['totalAsistencias'];
                    } else {
                        echo '<span class="na-value">No disponible</span>';
                    }
                    ?>
                </span>
            </div>
            <div class="stat-box">
                <span class="stat-title">MVPs:</span>
                <span class="stat-value">
                    <?php 
                    if (isset($estadisticas['totalMvps']) && $estadisticas['totalMvps'] > 0) {
                        echo $estadisticas['totalMvps'];
                    } else {
                        echo '<span class="na-value">No disponible</span>';
                    }
                    ?>
                </span>
            </div>
            <p><a href="<?php echo htmlspecialchars($statsUrl); ?>" style="color: #1abc9c;">Ver estadísticas completas</a></p>
        <?php endif; ?>

        <?php if (!empty($partidas_kdRatioData)): ?>
            <div class="chart-container">
                <h3>Evolución de K/D Ratio</h3>
                <canvas id="kdRatioChart"></canvas>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('kdRatioChart').getContext('2d');
                    const kdRatioChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($fechasPartidas); ?>,
                            datasets: [{
                                label: 'K/D Ratio',
                                data: <?php echo json_encode($partidas_kdRatioData); ?>,
                                backgroundColor: 'rgba(26, 188, 156, 0.5)',
                                borderColor: '#1abc9c',
                                borderWidth: 2,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            </script>

            <div class="comparison-container">
                <h3>Comparación de K/D Ratio</h3>
                <div class="stat-compare-box">
                    <div class="stat-name">K/D Ratio</div>
                    <div class="stat-bar-container">
                        <div class="stat-bar-global" style="width: 100%;">1.0 (global)</div>
                        <div class="stat-bar-user" style="width: <?php echo ($userKdRatio > 0) ? round(($userKdRatio / 1.0) * 100) : 0; ?>%;"> <?php echo $userKdRatio; ?> (tú)</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="feedback-container">
            <h3>Comentarios y Sugerencias</h3>
            <form method="POST" action="submit_feedback.php">
                <textarea name="feedback" rows="4" placeholder="Escribe tus comentarios aquí..." required></textarea>
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>