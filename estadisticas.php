<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

require_once("config.php");
require_once("SteamAPI.php");
$estadisticas = null; 

$kdRatioData = [0, 0, 0, 0, 0];
$kdRatioLabels = ['Sin datos', 'Sin datos', 'Sin datos', 'Sin datos', 'Sin datos'];

try {
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $dbname = "bkwgpnt7d5hd7bpuiwbw";
    $usuario = "uq6vff78pyt2g5lo";
    $contrasena = "u6l50PObWFQEFcpTIp5a";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    
    if (!$estadisticas) {
        $estadisticas = [
            'totalKills' => 0,
            'totalMuertes' => 0,
            'totalAsistencias' => 0,
            'totalHeadshots' => 0,
            'totalMvps' => 0,
        ];
    }
    
    $userKdRatio = ($estadisticas['totalMuertes'] > 0) ? 
                    round($estadisticas['totalKills'] / $estadisticas['totalMuertes'], 2) : 
                    $estadisticas['totalKills'];
    
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
    
    $partidas_kdRatioData = [];
    $fechasPartidas = [];
    
    if (!empty($ultimasPartidas)) {
        foreach (array_reverse($ultimasPartidas) as $partida) {
            $partidaKdRatio = ($partida['muertes'] > 0) ? round($partida['kills'] / $partida['muertes'], 2) : 
                            $partida['kills'];
            $partidas_kdRatioData[] = $partidaKdRatio;
            $fechasPartidas[] = date('d-m-Y', strtotime($partida['fecha']));
        }
    }

} catch (PDOException $e) {
    error_log("Error en estadisticas.php: " . $e->getMessage());
    $error = "Error de conexión: " . $e->getMessage();
}

$error = '';
$apiError = false;
$playerData = null;
$csgoStats = null;
$formattedStats = null;
$statsUrl = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id'])) {
    $steamId = trim($_POST['steam_id']);
    
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $steamAPI = new SteamAPI();
        
        $playerSummary = $steamAPI->getPlayerSummary($steamId);
        
        if (!$playerSummary) {
            $error = 'No se pudo encontrar información del jugador. Por favor, verifica el Steam ID.';
        } else {
            $playerData = $playerSummary;
            $statsUrl = "https://csstats.gg/player/{$steamId}";
            
            $rawStats = $steamAPI->getCS2Stats($steamId);
            
            if ($rawStats && isset($rawStats['playerstats']['stats'])) {
                $csgoStats = $rawStats['playerstats']['stats'];
                
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
                
                if ($formattedStats['totalRounds'] > 0) {
                    $formattedStats['winRate'] = round(($formattedStats['totalWins'] / $formattedStats['totalRounds']) * 100);
                }
                
                if ($formattedStats['totalDeaths'] > 0) {
                    $kdRatio = round($formattedStats['totalKills'] / $formattedStats['totalDeaths'], 2);
                } else {
                    $kdRatio = $formattedStats['totalKills'];
                }
                $kdRatioData = [$kdRatio, 1.0];
                $kdRatioLabels = ['Tu K/D', 'K/D Global'];
            } else {
                $apiError = true;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de CS2</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6. 0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #ffca28;
            --background-dark: #121212;
            --background-medium: #1c1c1c;
            --background-light: #282828;
            --text-light: #e0e0e0;
            --accent-color: #ff5722;
            --accent-hover: #ff784e;
            --border-color: #333;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            --transition-speed: 0.3s;
        }
        
        body {
            background-color: #1e1e1e;
            font-family: 'Poppins', sans-serif;
            color: var(--text-light);
        }
        
        header {
            background-color: var(--background-medium);
            color: var(--primary-color);
            padding: 15px 30px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
        }
        
        .logo {
            margin-right: 30px;
        }
        
        .logo img {
            height: 50px;
            margin-right: 20px;
            transition: transform var(--transition-speed) ease;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }
        
        .navigation {
            display: flex;
            gap: 15px;
        }
        
        .navigation a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            transition: all var(--transition-speed) ease;
            border-radius: 5px;
            display: flex; align-items: center;
        }
        
        .navigation a i {
            margin-right: 5px;
        }
        
        .navigation a:hover {
            background-color: rgba(255, 202, 40, 0.1);
            color: var(--primary-color);
        }
        
        .navigation a.active {
            background-color: rgba(255, 202, 40, 0.2);
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .btn-logout {
            margin-left: auto;
            padding: 8px 15px;
            background-color: #ff5722;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-logout i {
            margin-right: 5px;
        }
        
        .btn-logout:hover {
            background-color: #ff784e;
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
                    <img src="/img/logo-removebg-preview.png" alt="Logo"> 
                </div>
                <div class="navigation">
                    <a href="principal.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="estadisticas.php" class="active"><i class="fas fa-chart-line"></i> Estadísticas</a>
                    <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
                    <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
                </div>
            </div>
            <div class="nav-right">
                <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
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

    </div>

    <footer>
        <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>