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

try {
    // Establecer conexión a la base de datos
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $dbname = "bkwgpnt7d5hd7bpuiwbw";
    $usuario = "uq6vff78pyt2g5lo";
    $contrasena = "u6l50PObWFQEFcpTIp5a";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener estadísticas del usuario
    $stmt = $pdo->prepare("SELECT SUM(kills) as totalKills, SUM(muertes) as totalMuertes, SUM(victorias) as victorias, SUM(derrotas) as derrotas FROM Estadisticas WHERE idUsuario = :idUsuario");
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $estadisticas = $stmt->fetch();
    
    // Si no hay estadísticas, establecer valores predeterminados
    if (!$estadisticas) {
        $estadisticas = [
            'totalKills' => 0,
            'totalMuertes' => 0,
            'victorias' => 0,
            'derrotas' => 0,
        ];
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #1e1e1e;
            color: #f0f0f0;
        }
        .content {
            max-width: 800px;
            margin: 20px auto;
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
                <span class="stat-title">Tasa de Victorias:</span>
                <span class="stat-value"><?php echo $formattedStats['winRate']; ?>%</span>
            </div>
            <div class="stat-box">
                <span class="stat-title">Headshots:</span>
                <span class="stat-value"><?php echo $formattedStats['headshots']; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-title">Asistencias:</span>
                <span class="stat-value"><?php echo $formattedStats['assists']; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-title">MVPs:</span>
                <span class="stat-value"><?php echo $formattedStats['MVPs']; ?></span>
            </div>
            <p><a href="<?php echo htmlspecialchars($statsUrl); ?>" style="color: #1abc9c;">Ver estadísticas completas</a></p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>