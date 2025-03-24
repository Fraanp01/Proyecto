<?php
require_once 'SteamAPI.php';

$error = '';
$ownedGames = null;
$playerData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['steam_id'])) {
    $steamId = trim($_POST['steam_id']);
    
    if (!preg_match('/^[0-9]{17}$/', $steamId)) {
        $error = 'Por favor, introduce un Steam ID válido (17 dígitos)';
    } else {
        $api = new SteamAPI();
        $playerData = $api->getPlayerSummary($steamId);
        $ownedGames = $api->getOwnedGames($steamId);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Juegos de Steam</title>
    <link rel="stylesheet" href="css.css">
    <style>
        .game-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
        }
        .game-image {
            width: 120px;
            height: 45px;
            margin-right: 15px;
            object-fit: cover;
        }
        .game-info {
            flex-grow: 1;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .game-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .game-stats div {
            text-align: center;
            padding: 5px 10px;
            background-color: #e9e9e9;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .search-form {
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .search-form input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #45a049;
        }
        .player-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .player-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
        }
        .player-details {
            flex-grow: 1;
        }
        .games-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .games-filter {
            display: flex;
            align-items: center;
        }
        .games-filter select {
            margin-left: 10px;
            padding: 5px;
            border-radius: 3px;
        }
        </style>
</head>
<body>
    <div class="container">
        <h1>Estadísticas de Juegos de Steam</h1>
        
        <div class="search-form">
            <form method="POST" action="">
                <label for="steam_id">Steam ID (17 dígitos):</label>
                <input type="text" id="steam_id" name="steam_id" placeholder="76561198xxxxxxxxx" required>
                <button type="submit">Buscar Jugador</button>
            </form>
            <p>¿No sabes tu Steam ID? <a href="#" onclick="showHelp(); return false;">Haz clic aquí</a> para saber cómo encontrarlo.</p>
            <div id="help-box" style="display: none; margin-top: 10px; padding: 10px; background-color: #e8f4f8; border-radius: 5px;">
                <p>Para encontrar tu Steam ID:</p>
                <ol>
                    <li>Abre tu perfil de Steam en el navegador</li>
                    <li>La URL será algo como: <code>https://steamcommunity.com/id/TUUSUARIO/</code> o <code>https://steamcommunity.com/profiles/76561198XXXXXXXXX/</code></li>
                    <li>Si tu URL tiene "profiles" seguido de un número, ese número es tu Steam ID</li>
                    <li>Si tu URL tiene "id" seguido de un nombre, necesitarás usar un <a href="https://steamid.io/" target="_blank">convertidor de Steam ID</a></li>
                </ol>
            </div>
        </div>
        
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if ($playerData && isset($playerData['response']['players'][0])): ?>
            <?php $player = $playerData['response']['players'][0]; ?>
            
            <div class="player-info">
                <img src="<?php echo htmlspecialchars($player['avatarfull']); ?>" alt="Avatar" class="player-avatar">
                <div class="player-details">
                    <h2><?php echo htmlspecialchars($player['personaname']); ?></h2>
                    <p>
                        <strong>Estado:</strong> 
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
                    <p><a href="<?php echo htmlspecialchars($player['profileurl']); ?>" target="_blank">Ver perfil completo en Steam</a></p>
                </div>
            </div>
            
            <?php if ($ownedGames && isset($ownedGames['response']['games'])): ?>
                <div class="games-header">
                    <h2>Juegos del jugador (<?php echo count($ownedGames['response']['games']); ?> juegos)</h2>
                    <div class="games-filter">
                        <label for="sort-games">Ordenar por:</label>
                        <select id="sort-games" onchange="sortGames(this.value)">
                        <option value="playtime">Tiempo de juego</option>
                            <option value="name">Nombre</option>
                            <option value="last_played">Última vez jugado</option>
                        </select>
                    </div>
                </div>
                
                <div id="games-container">
                    <?php 
                    usort($ownedGames['response']['games'], function($a, $b) {
                        return $b['playtime_forever'] - $a['playtime_forever'];
                    });
                    
                    foreach ($ownedGames['response']['games'] as $game): 
                    ?>
                        <div class="game-card" data-name="<?php echo htmlspecialchars($game['name']); ?>" data-playtime="<?php echo $game['playtime_forever']; ?>" data-last-played="<?php echo isset($game['rtime_last_played']) ? $game['rtime_last_played'] : 0; ?>">
                            <img src="https://steamcdn-a.akamaihd.net/steam/apps/<?php echo $game['appid']; ?>/capsule_184x69.jpg" alt="<?php echo htmlspecialchars($game['name']); ?>" class="game-image" onerror="this.src='placeholder.jpg'">
                            <div class="game-info">
                                <h3><?php echo htmlspecialchars($game['name']); ?></h3>
                                <div class="game-stats">
                                    <div>
                                        <strong>Tiempo de juego:</strong> 
                                        <?php 
                                        $minutes = $game['playtime_forever'];
                                        $hours = floor($minutes / 60);
                                        $remainingMinutes = $minutes % 60;
                                        echo "{$hours}h {$remainingMinutes}m"; 
                                        ?>
                                    </div>
                                    <?php if (isset($game['rtime_last_played']) && $game['rtime_last_played'] > 0): ?>
                                        <div>
                                            <strong>Última vez jugado:</strong> 
                                            <?php echo date('d/m/Y', $game['rtime_last_played']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (isset($game['playtime_2weeks'])): ?>
                                        <div>
                                            <strong>Últimas 2 semanas:</strong> 
                                            <?php 
                                            $minutes = $game['playtime_2weeks'];
                                            $hours = floor($minutes / 60);
                                            $remainingMinutes = $minutes % 60;
                                            echo "{$hours}h {$remainingMinutes}m"; 
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <script>
                function showHelp() {
                    var helpBox = document.getElementById('help-box');
                    if (helpBox.style.display === 'none') {
                        helpBox.style.display = 'block';
                    } else {
                        helpBox.style.display = 'none';
                    }
                }
                
                function sortGames(criteria) {
                    var container = document.getElementById('games-container');
                    var games = Array.from(container.getElementsByClassName('game-card'));
                    
                    games.sort(function(a, b) {
                        if (criteria === 'name') {
                            var nameA = a.getAttribute('data-name').toLowerCase();
                            var nameB = b.getAttribute('data-name').toLowerCase();
                            return nameA.localeCompare(nameB);
                        } else if (criteria === 'playtime') {
                            return parseInt(b.getAttribute('data-playtime')) - parseInt(a.getAttribute('data-playtime'));
                        } else if (criteria === 'last_played') {
                            return parseInt(b.getAttribute('data-last-played')) - parseInt(a.getAttribute('data-last-played'));
                        }
                        return 0;
                    });
                    
                    while (container.firstChild) {
                        container.removeChild(container.firstChild);
                    }
                    
                    games.forEach(function(game) {
                        container.appendChild(game);
                    });
                }
                </script>
                
            <?php else: ?>
                <div class="game-card">
                    <p>No se pudieron obtener los juegos para este jugador. Esto puede deberse a que:</p>
                    <ul>
                        <li>El perfil del jugador está configurado como privado</li>
                        <li>El jugador no tiene juegos</li>
                        <li>Hay un problema con la API de Steam</li>
                    </ul>
                    <p>Si el perfil es privado, el usuario debe cambiar la configuración de privacidad en Steam:</p>
                    <ol>
                        <li>Ir a la página de perfil de Steam</li>
                        <li>Hacer clic en "Editar Perfil"</li>
                        <li>Ir a "Configuración de privacidad"</li>
                        <li>Cambiar "Detalles del juego" a "Público"</li>
                    </ol>
                </div>
            <?php endif; ?>
            
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="game-card">
                <p>No se encontró ningún jugador con ese Steam ID. Por favor, verifica que el ID sea correcto.</p>
                <p>Nota: El Steam ID debe ser el identificador numérico de 17 dígitos, no el nombre de usuario.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>