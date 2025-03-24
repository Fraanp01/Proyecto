<?php
require_once 'SteamAPI.php';
require_once 'CSGOStatsScrapper.php'; 

$error = '';
$playerData = null;
$csgoStats = null;
$mapStats = null;
$detailedMapStats = null;
$selectedMap = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id']) && empty($_POST['map_name'])) {
    $steamId = trim($_POST['steam_id']);
    
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $api = new SteamAPI();
        $playerData = $api->getPlayerSummary($steamId);
        
        $csgoStats = $api->getCSGOStats($steamId);
        
        try {
            $scraper = new CSGOStatsScrapper();
            $scrapedData = $scraper->getPlayerStats($steamId);
            
            if (is_array($scrapedData) && !isset($scrapedData['error'])) {
                $mapStats = $scrapedData;
            } else {
                if (isset($scrapedData['error'])) {
                    $error .= " Error al obtener estadísticas de mapas: " . $scrapedData['error'];
                } else {
                    $error .= " Error: formato de datos de mapas inesperado.";
                }
            }
        } catch (Exception $e) {
            $error .= " Error al acceder a CSGOSTATS.GG: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['map_name']) && !empty($_POST['steam_id'])) {
    $selectedMap = trim($_POST['map_name']);
    $steamId = trim($_POST['steam_id']);
    
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $api = new SteamAPI();
        $playerData = $api->getPlayerSummary($steamId);
        
        $csgoStats = $api->getCSGOStats($steamId);
        
        
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Jugadores de Steam</title>
    <link rel="stylesheet" href="css.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
            background-color: var(--background-dark);
            color: var(--text-light);
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 800px; 
            margin: 80px auto;
            background: var(--background-medium);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }

        h1, h2, h3 {
            color: var(--primary-color);
            text-align: center;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }

        .player-card {
            border: 1px solid var(--border-color);
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            background-color: #2a2a2a;
        }

        .search-section {
            background-color: var(--accent-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            text-align: center;
            margin-bottom: 20px;
        }

        .search-section input[type="text"],
        .search-section select {
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 70%;
            margin-top: 10px;
        }

        .search-section button {
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: #fff; border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color var(--transition-speed);
        }

        .search-section button:hover {
            background-color: var(--accent-hover);
        }

        .maps-section {
            margin-top: 30px;
        }

        .map-stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .map-stats-table th,
        .map-stats-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .map-stats-table th {
            background-color: var(--background-light);
            color: var(--primary-color);
        }

        .stat-card {
            background-color: #2a2a2a;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            box-shadow: var(--box-shadow);
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: var(--primary-color);
        }

        .stat-card p {
            margin: 0;
            font-size: 14px;
            color: var(--text-light);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-section">
            <h1>Búsqueda de Jugadores de Steam</h1>
            <form method="POST" action="">
                <input type="text" id="steam_id" name="steam_id" placeholder="76561198xxxxxxxxx" required>
                <label for="map_name">Selecciona un mapa para estadísticas detalladas:</label>
                <select name="map_name" id="map_name">
                    <option value="">--Selecciona un mapa--</option>
                    <option value="Dust II">Dust II</option>
                    <option value="Mirage">Mirage</option>
                    <option value="Inferno">Inferno</option>
                </select>
                <button type="submit">Buscar Jugador</button>
            </form>
        </div>
        
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if ($playerData): ?>
            <div class="player-card <h2>Información del Jugador</h2>
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
                <div class="player-card">
                    <h2>Estadísticas de CS:GO</h2>
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

            <?php if ($mapStats && is_array($mapStats) && isset($mapStats['maps']) && is_array($mapStats['maps'])): ?>
                <div class="maps-section">
                    <h3>Estadísticas por Mapa</h3>
                    <table class="map-stats-table">
                        <thead>
                            <tr>
                                <th>Mapa</th>
                                <th>Partidas</th>
                                <th>Tasa de Victorias</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mapStats['maps'] as $mapName => $stats): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($mapName); ?></td>
                                    <td><?php echo htmlspecialchars($stats['matches']); ?></td>
                                    <td><?php echo htmlspecialchars($stats['winRate']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($detailedMapStats): ?>
                <h2>Estadísticas Detalladas de <?php echo htmlspecialchars($selectedMap); ?></h2>
                <p>Total de Partidos: <?php echo htmlspecialchars($detailedMapStats['totalMatches']); ?></p>
                <p>Tasa de Victorias: <?php echo htmlspecialchars($detailedMapStats['winRate']); ?></p>
                <p>Puntuación Promedio: <?php echo htmlspecialchars($detailedMapStats['avgScore']); ?></p>
                <p>Mejor Lado: <?php echo htmlspecialchars($detailedMapStats['bestSide']); ?></p>
                
                <h3>Posiciones Comunes</h3>
                <ul>
                    <?php foreach ($detailedMapStats['commonPositions'] as $position => $rate): ?>
                        <li><?php echo htmlspecialchars($position); ?>: <?php echo htmlspecialchars($rate); ?></li>
                    <?php endforeach; ?>
                </ul>

                <h3>Uso de Armas</h3>
                <ul>
                    <?php foreach ($detailedMapStats['weaponUsage'] as $weapon => $usage): ?>
                        <li><?php echo htmlspecialchars($weapon); ?>: <?php echo htmlspecialchars($usage); ?></li>
                    <?php endforeach; ?>
                </ul>

                <h3>Partidos Recientes</h3>
                <ul>
                    <?php foreach ($detailedMapStats['recentMatches'] as $match): ?>
                        <li><?php echo htmlspecialchars($match['date']); ?> - <?php echo htmlspecialchars($match['result']); ?> (<?php echo htmlspecialchars($match['score']); ?>) - Kills: <?php echo htmlspecialchars($match['kills']); ?>, Muertes: <?php echo htmlspecialchars($match['deaths']); ?>, MVPs: <?php echo htmlspecialchars($match['mvps']); ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (isset($detailedMapStats['mapSpecificStats'])): ?>
                    <h3>Estadísticas Específicas del Mapa</h3>
                    <ul>
                        <?php foreach ($detailedMapStats['mapSpecificStats'] as $stat => $value): ?>
                            <li><?php echo htmlspecialchars($stat); ?>: <?php echo htmlspecialchars($value); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <p class="error">No se pudo encontrar al jugador. Asegúrate de que el Steam ID sea correcto y que el perfil sea público.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
            