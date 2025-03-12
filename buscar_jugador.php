<?php
// buscar_jugador.php
require_once 'SteamAPI.php';

$error = '';
$playerData = null;
$csgoStats = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id'])) {
    $steamId = trim($_POST['steam_id']);
    
    // Validación básica del Steam ID
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $api = new SteamAPI();
        $playerData = $api->getPlayerSummary($steamId);
        
        // Intentamos obtener las estadísticas de CS:GO
        $csgoStats = $api->getCS2Stats($steamId);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Jugadores de Steam</title>
    <link rel="stylesheet" href="tu_archivo_css.css">
    <style>
        .player-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .stats-table th, .stats-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .stats-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Búsqueda de Jugadores de Steam</h1>
    
    <form method="POST" action="">
        <div>
            <label for="steam_id">Steam ID (17 dígitos):</label>
            <input type="text" id="steam_id" name="steam_id" placeholder="76561198xxxxxxxxx" required>
        </div>
        <button type="submit">Buscar Jugador</button>
    </form>
    
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <?php if ($playerData && isset($playerData['response']['players'][0])): ?>
        <?php $player = $playerData['response']['players'][0]; ?>
        <div class="player-card">
            <h2>Información del Jugador</h2>
            <div>
                <img src="<?php echo htmlspecialchars($player['avatarfull']); ?>" alt="Avatar" style="width: 100px; height: 100px;">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($player['personaname']); ?></p>
                <p><strong>Estado:</strong> 
                    <?php 
                    $status = '';
                    switch($player['personastate']) {
                        case 0: $status = 'Desconectado'; break;
                        case 1: $status = 'En línea'; break;
                        case 2: $status = 'Ocupado'; break;
                        case 3: $status = 'Ausente'; break;
                        case 4: $status = 'Dormido'; break;
                        case 5: $status = 'Buscando intercambio'; break;
                        case 6: $status = 'Buscando jugar'; break;
                        default: $status = 'Desconocido';
                    }
                    echo $status;
                    ?>
                </p>
                <p><strong>Perfil:</strong> <a href="<?php echo htmlspecialchars($player['profileurl']); ?>" target="_blank">Ver perfil en Steam</a></p>
                <?php if (isset($player['realname'])): ?>
                    <p><strong>Nombre real:</strong> <?php echo htmlspecialchars($player['realname']); ?></p>
                <?php endif; ?>
                <?php if (isset($player['loccountrycode'])): ?>
                    <p><strong>País:</strong> <?php echo htmlspecialchars($player['loccountrycode']); ?></p>
                <?php endif; ?>
                <p><strong>Cuenta creada:</strong> <?php echo date('d/m/Y', $player['timecreated']); ?></p>
            </div>
        </div>
        
        <?php if ($csgoStats && isset($csgoStats['playerstats']['stats'])): ?>
            <div class="player-card">
                <h2>Estadísticas de CS2</h2>
                <table class="stats-table">
                    <tr>
                        <th>Estadística</th>
                        <th>Valor</th>
                    </tr>
                    <?php foreach ($csgoStats['playerstats']['stats'] as $stat): ?>
                        <?php 
                        // Mostrar solo algunas estadísticas relevantes
                        $statName = $stat['name'];
                        $displayName = '';
                        
                        switch ($statName) {
                            case 'total_kills': $displayName = 'Asesinatos totales'; break;
                            case 'total_deaths': $displayName = 'Muertes totales'; break;
                            case 'total_time_played': $displayName = 'Tiempo jugado (segundos)'; break;
                            case 'total_wins': $displayName = 'Victorias totales'; break;
                            case 'total_matches_played': $displayName = 'Partidas jugadas'; break;
                            case 'total_shots_fired': $displayName = 'Disparos realizados'; break;
                            case 'total_shots_hit': $displayName = 'Disparos acertados'; break;
                            case 'total_damage_done': $displayName = 'Daño total realizado'; break;
                            case 'last_match_kills': $displayName = 'Asesinatos última partida'; break;
                            case 'last_match_deaths': $displayName = 'Muertes última partida'; break;
                            case 'total_mvps': $displayName = 'Total MVPs'; break;
                            default: continue; // Saltar estadísticas no relevantes
                        }
                        ?>
                        <tr>
                            <td><?php echo $displayName; ?></td>
                            <td><?php echo $stat['value']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                
                <?php 
                // Calcular KD Ratio si tenemos asesinatos y muertes
                $kills = 0;
                $deaths = 0;
                
                foreach ($csgoStats['playerstats']['stats'] as $stat) {
                    if ($stat['name'] == 'total_kills') {
                        $kills = $stat['value'];
                    }
                    if ($stat['name'] == 'total_deaths') {
                        $deaths = $stat['value'];
                    }
                }
                
                if ($deaths > 0) {
                    $kdRatio = round($kills / $deaths, 2);
                    echo "<p><strong>K/D Ratio:</strong> {$kdRatio}</p>";
                }
                
                // Calcular precisión si tenemos disparos realizados y acertados
                $shotsFired = 0;
                $shotsHit = 0;
                
                foreach ($csgoStats['playerstats']['stats'] as $stat) {
                    if ($stat['name'] == 'total_shots_fired') {
                        $shotsFired = $stat['value'];
                    }
                    if ($stat['name'] == 'total_shots_hit') {
                        $shotsHit = $stat['value'];
                    }
                }
                
                if ($shotsFired > 0) {
                    $accuracy = round(($shotsHit / $shotsFired) * 100, 2);
                    echo "<p><strong>Precisión:</strong> {$accuracy}%</p>";
                }
                ?>
            </div>
        <?php elseif ($playerData): ?>
            <div class="player-card">
    <h2>Estadísticas de CS2</h2>
    <p>No se pudieron obtener las estadísticas de CS2 para este jugador. Esto puede deberse a que:</p>
    <ul>
        <li>El perfil del jugador está configurado como privado</li>
        <li>El jugador no tiene CS2</li>
        <li>El jugador no ha jugado a CS2 recientemente</li>
    </ul>
</div>
        <?php endif; ?>
        
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="player-card">
            <p>No se encontró ningún jugador con ese Steam ID. Por favor, verifica que el ID sea correcto.</p>
            <p>Nota: El Steam ID debe ser el identificador numérico de 17 dígitos, no el nombre de usuario.</p>
            <p>Puedes encontrar tu Steam ID:</p>
            <ol>
                <li>Abre tu perfil de Steam</li>
                <li>Haz clic derecho en cualquier lugar de la página y selecciona "Inspeccionar"</li>
                <li>Busca algo como "profiles/76561198XXXXXXXXX" - ese número es tu Steam ID</li>
            </ol>
        </div>
    <?php endif; ?>
</body>
</html>
