<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Incluir archivo de configuración de base de datos
require_once("config.php");
require_once("SteamAPI.php"); // Asegúrate de que este archivo existe
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
$playerData = null;
$csgoStats = null;

// Procesar la búsqueda del jugador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id'])) {
    $steamId = trim($_POST['steam_id']);
    
    // Validación básica del Steam ID
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $api = new SteamAPI();
        $playerData = $api->getPlayerSummary($steamId);
        
        // Intentar obtener las estadísticas de CS:GO
        $csgoStats = $api->getCSGOStats($steamId);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - CStats</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            background-color: var(--background-dark);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
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
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        }

        .navigation {
            display: flex;
            gap: 20px;
            margin-right: 15px;
        }

        .navigation a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            transition: all var(--transition-speed) ease;
            border-radius: 5px;
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

        .content {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat {
            background-color: var(--background-medium);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--box-shadow);
            flex: 1 1 calc(25% - 20px);
            margin: 10px;
            text-align: center;
        }

        .search-player {
            background-color: var(--background-medium);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .search-player input {
            width: calc(100% - 100px);
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-right: 10px;
        }

        .search-player button {
            padding: 10px 20px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .search-player button:hover {
            background-color: var(--accent-hover);
        }

        .player-card, .player-stats {
            background-color: var(--background-medium);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-top: 20px;
            text-align: center;
        }

        .stat-card {
            background-color: rgba(255, 202, 40, 0.1);
            border-radius: 10px;
            padding: 15px;
            transition: transform var(--transition-speed) ease;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        footer {
            background-color: var(--background-medium);
            color: var(--text-light);
            text-align: center;
            padding: 15px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
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
                    <a href="estrategias.php">Estrategias</a>
                    <a href="feedback.php">Feedback</a>
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="content">
        <h1>Estadísticas Generales</h1>
        <div class="stats">
            <div class="stat">
                <h3>K/D Ratio</h3>
                <p>
    <?php 
    if ($estadisticas && isset($estadisticas['totalMuertes']) && $estadisticas['totalMuertes'] > 0) {
        echo round($estadisticas['totalKills'] / $estadisticas['totalMuertes'], 2);
    } else {
        echo "N/A";
    }
    ?>
</p>
            </div>
            <div class="stat">
                <h3>Victorias</h3>
                <p><?php echo $estadisticas['victorias'] ?? 0; ?></p> 
            </div>
            <div class="stat">
                <h3>Derrotas</h3>
                <p><?php echo $estadisticas['derrotas'] ?? 0; ?></p>
            </div>
            <div class="stat">
                <h3>Total Kills</h3>
                <p><?php echo $estadisticas['totalKills'] ?? 0; ?></p>
            </div>
        </div>

        <div class="search-player">
            <h2>Búsqueda de Jugadores</h2>
            <form method="POST" action="">
                <input type="text" id="steam_id" name="steam_id" placeholder="76561198xxxxxxxxx" required>
                <button type="submit">Buscar Jugador</button>
            </form>

            <?php if ($playerData): ?>
                <div class="player-card">
                    <h3>Información del Jugador</h3>
                    <p>Nombre: <?php echo isset($playerData['personaname']) ? htmlspecialchars($playerData['personaname']) : 'No disponible'; ?></p>
                    <p>Estado: <?php 
                        if (isset($playerData['personastate'])) {
                            $status = 'Desconocido';
                            switch($playerData['personastate']) {
                                case 0: $status = 'Desconectado'; break;
                                case 1: $status = 'En línea'; break;
                                case 2: $status = 'Ocupado'; break;
                                case 3: $status = 'Ausente'; break;
                                case 4: $status = 'Durmiendo'; break;
                                case 5: $status = 'Deseando intercambiar'; break;
                                case 6: $status = 'Deseando jugar'; break;
                            }
                            echo htmlspecialchars($status);
                        } else {
                            echo 'No disponible';
                        }
                    ?></p>
                    
                    <?php if (isset($playerData['avatar'])): ?>
                        <p><img src="<?php echo htmlspecialchars($playerData['avatar']); ?>" alt="Avatar" width="100"></p>
                    <?php else: ?>
                        <p>Avatar no disponible</p>
                    <?php endif; ?>
                </div>

                <?php if ($csgoStats && isset($csgoStats['playerstats']) && isset($csgoStats['playerstats']['stats'])): ?>
                    <div class="player-stats">
                        <h3>Estadísticas de CS:GO</h3>
                        <div class="stats-grid">
                            <?php
                            $statsMapping = [
                                'total_kills' => 'Kills',
                                'total_deaths' => 'Deaths',
                                'total_wins' => 'Victorias',
                                'total_rounds_played' => 'Rondas Jugadas',
                                'total_shots_fired' => 'Disparos Realizados',
                                'total_shots_hit' => 'Disparos Acertados',
                                'total_kills_headshot' => 'Headshots',
                                'total_mvps' => 'MVPs',
                                'total_time_played' => 'Tiempo Jugado (segundos)',
                            ];

                            $statsData = [];
                            foreach ($csgoStats['playerstats']['stats'] as $stat) {
                                if (isset($stat['name']) && isset($stat['value'])) {
                                    $statsData[$stat['name']] = $stat['value'];
                                }
                            }

                            foreach ($statsMapping as $statKey => $statName) {
                                if (array_key_exists($statKey, $statsData)) {
                                    echo '<div class="stat-card">';
                                    echo '<h3>' . htmlspecialchars($statName) . '</h3>';
                                    echo '<p>' . htmlspecialchars($statsData[$statKey]) . '</p>';
                                    echo '</div>';
                                }
                            }

                            if (isset($statsData['total_kills']) && isset($statsData['total_deaths']) && $statsData['total_deaths'] > 0) {
                                $kdRatio = round($statsData['total_kills'] / $statsData['total_deaths'], 2);
                                echo '<div class="stat-card">';
                                echo '<h3>K/D Ratio</h3>';
                                echo '<p>' . htmlspecialchars($kdRatio) . '</p>';
                                echo '</div>';
                            }

                            if (isset($statsData['total_shots_fired']) && isset($statsData['total_shots_hit']) && $statsData['total_shots_fired'] > 0) {
                                $accuracy = round(($statsData['total_shots_hit'] / $statsData['total_shots_fired']) * 100, 2);
                                echo '<div class="stat-card">';
                                echo '<h3>Precisión</h3>';
                                echo '<p>' . htmlspecialchars($accuracy) . '%</p>';
                                echo '</div>';
                            }

                            if (isset($statsData['total_kills']) && isset($statsData['total_kills_headshot']) && $statsData['total_kills'] > 0) {
                                $headshotPercentage = round(($statsData['total_kills_headshot'] / $statsData['total_kills']) * 100, 2);
                                echo '<div class="stat-card">';
                                echo '<h3>Porcentaje de Headshots</h3>';
                                echo '<p>' . htmlspecialchars($headshotPercentage) . '%</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="error">No se encontraron estadísticas de CS:GO para este jugador.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>