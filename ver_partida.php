<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";
$pdo = conectarDB();

// Verificar si se proporciona un ID de partida
if (!isset($_GET["id"])) {
    header("Location: partidas.php");
    exit();
}

$idPartida = $_GET["id"];
$idUsuario = $_SESSION["idUsuario"];
$esCoach = isset($_SESSION["role"]) && $_SESSION["role"] === 'coach';

// Inicializar variables para evitar warnings
$videos = [];
$mensaje = "";
$error = "";
$apuntes = "";
$mensajeApuntes = "";

// Crear tabla para videos de YouTube si no existe
try {
    $sql = "CREATE TABLE IF NOT EXISTS VideosYoutube (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idPartida INT NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        codigo_youtube VARCHAR(255) NOT NULL,
        fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idPartida) REFERENCES Partida(idPartida) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Crear tabla para apuntes del coach si no existe
    $sql = "CREATE TABLE IF NOT EXISTS ApuntesCoach (
        id INT AUTO_INCREMENT PRIMARY KEY,
        idPartida INT NOT NULL,
        apuntes TEXT NOT NULL,
        fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (idPartida) REFERENCES Partida(idPartida) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
} catch (PDOException $e) {
    // Si hay error, continuar
}

// Procesar formulario para agregar video
if ($esCoach && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["codigo_youtube"])) {
    $titulo = trim($_POST["titulo"]);
    $codigoYoutube = trim($_POST["codigo_youtube"]);
    
    // Validar los datos
    if (empty($titulo) || empty($codigoYoutube)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        // Extraer el código de YouTube si se pegó una URL completa
        if (strpos($codigoYoutube, 'youtube.com') !== false || strpos($codigoYoutube, 'youtu.be') !== false) {
            if (preg_match('/[\/\=]([a-zA-Z0-9_-]{11})/', $codigoYoutube, $matches)) {
                $codigoYoutube = $matches[1];
            }
        }
        
        // Verificar que el código tenga 11 caracteres (formato estándar de YouTube)
        if (strlen($codigoYoutube) !== 11) {
            $error = "El código de YouTube no es válido. Debe tener 11 caracteres.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO VideosYoutube (idPartida, titulo, codigo_youtube) VALUES (?, ?, ?)");
                $stmt->execute([$idPartida, $titulo, $codigoYoutube]);
                $mensaje = "Video agregado correctamente.";
            } catch (PDOException $e) {
                $error = "Error al guardar el video: " . $e->getMessage();
            }
        }
    }
}

// Procesar formulario para agregar o actualizar apuntes del coach
if ($esCoach && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["apuntes"])) {
    $apuntes = trim($_POST["apuntes"]); 
    try {
         // Verificar si ya existen apuntes para la partida
        $stmt = $pdo->prepare("SELECT * FROM ApuntesCoach WHERE idPart ida = ?");
        $stmt->execute([$idPartida]);
        $existingApuntes = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingApuntes) {
            // Actualizar apuntes existentes
            $stmt = $pdo->prepare("UPDATE ApuntesCoach SET apuntes = ? WHERE idPartida = ?");
            $stmt->execute([$apuntes, $idPartida]);
            $mensajeApuntes = "Apuntes actualizados correctamente.";
        } else {
            // Insertar nuevos apuntes
            $stmt = $pdo->prepare("INSERT INTO ApuntesCoach (idPartida, apuntes) VALUES (?, ?)");
            $stmt->execute([$idPartida, $apuntes]);
            $mensajeApuntes = "Apuntes añadidos correctamente.";
        }
    } catch (PDOException $e) {
        $error = "Error al guardar los apuntes: " . $e->getMessage();
    }
}

// Obtener detalles de la partida
try {
    $stmt = $pdo->prepare("SELECT * FROM Partida WHERE idPartida = ?");
    $stmt->execute([$idPartida]);
    $partida = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$partida) {
        header("Location: partidas.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error al obtener los detalles de la partida: " . $e->getMessage();
}

// Obtener videos de la partida
try {
    $stmt = $pdo->prepare("SELECT * FROM VideosYoutube WHERE idPartida = ? ORDER BY fecha_agregado DESC");
    $stmt->execute([$idPartida]);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener los videos: " . $e->getMessage();
}

// Obtener apuntes del coach
try {
    $stmt = $pdo->prepare("SELECT * FROM ApuntesCoach WHERE idPartida = ?");
    $stmt->execute([$idPartida]);
    $apuntesExistentes = $stmt->fetch(PDO::FETCH_ASSOC);
    $apuntes = $apuntesExistentes ? $apuntesExistentes['apuntes'] : '';
} catch (PDOException $e) {
    $error = "Error al obtener los apuntes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($partida['titulo']) ? htmlspecialchars($partida['titulo']) : 'Título no disponible'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css.css">
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
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
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
            box-sizing: border-box;
        }
        .logo img {
            height: 50px;
            transition: transform var(--transition-speed) ease;
        }

        .logo img:hover {
            transform: scale(1.05);
        }

        .navigation {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-left: auto;
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

        .btn-logout {
            padding: 8px 15px;
            background-color: #ff5722;
            color: white !important;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #ff784e;
            color: white !important;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto;
            padding: 20px;
            background-color: var(--background-medium);
            border-radius: 10px;
            box-shadow: var(--box-shadow);
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }

        .partido-detalles, .videos {
            background-color: var(--background-light);
            border-radius: 5px;
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: transform var(--transition-speed);
        }

        .partido-detalles:hover, .videos:hover {
            transform: scale(1.02);
        }

        .info-partida {
            list-style: none;
            padding: 0;
        }

        .info-partida li {
            margin: 10px 0;
        }

        .error, .mensaje {
            color: var(--accent-color);
            text-align: center;
            margin: 10px 0;
        }

        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        form input {
            margin: 5px 0;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            transition: border-color var(--transition-speed);
        }

        form input:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        form button {
            padding: 10px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        form button:hover {
            background-color: var(--accent-hover);
        }

        iframe {
            width: 100%;
            height: 315px;
            border: none;
            border-radius: 5px;
            box-shadow: var(--box-shadow);
        }

        .apuntes-coach {
            background-color: var(--background-light);
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: var(--box-shadow);
            transition: transform var(--transition-speed);
        }

        .apuntes-coach:hover {
            transform: scale(1.02);
        }

        .coach-form {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .coach-form textarea {
            width: 100%;
            background-color: var(--background-medium);
            color: var(--text-light);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 12px;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }

        .coach-notes-display {
            display: flex;
            background-color: rgba(255, 202, 40, 0.05);
            border-left: 4px solid var(--primary-color);
            border-radius: 6px;
            padding: 20px;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .coach-notes-display:hover {
            background-color: rgba(255, 202, 40, 0.1);
        }

        .coach-avatar {
            font-size: 40px;
            color: var(--primary-color);
            margin-right: 15px;
        }

        .notes-content {
            flex: 1;
        }

        .notes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notes-header h3 {
            margin: 0;
            color : var(--primary-color);
        }

        .notes-date {
            font-size: 12px;
            color: var(--text-light);
        }

        .notes-body p {
            margin: 5px 0;
            line-height: 1.5;
        }

        .no-notes {
            text-align: center;
            color: var(--text-light);
            font-size: 14px;
            margin-top: 15px;
        }

        .titulo-container {
            position: relative;
            margin-bottom: 30px;
        }

        .btn-editar-titulo {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.8em;
            cursor: pointer;
            margin-left: 10px;
            transition: transform 0.2s ease;
        }

        .btn-editar-titulo:hover {
            transform: scale(1.2);
        }

        .form-editar-titulo {
            background-color: var(--background-light);
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            box-shadow: var(--box-shadow);
        }

        .form-editar-titulo input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            background-color: var(--background-medium);
            color: var(--text-light);
            border: 1px solid var(--border-color);
            border-radius: 5px;
        }

        .form-editar-titulo button {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-editar-titulo button[type="submit"] {
            background-color: var(--accent-color);
            color: white;
        }

        .form-editar-titulo button[type="button"] {
            background-color: var(--background-medium);
            color: var(--text-light);
        }
        .mapa-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.mapa-imagen {
    flex: 0 0 auto;
    max-width: 250px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.mapa-nombre {
    text-align: center;
    margin-top: 8px;
    font-weight: bold;
    color: var(--primary-color);
}

.mapa-imagen img {
    width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.info-partida {
    flex: 1;
    min-width: 250px;
    margin: 0;
    padding: 0;
    list-style: none;
}
.grafica-container {
    flex: 1;
    min-width: 300px;
    max-width: 400px;
    height: 250px;
    margin: 0 10px;
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<header>
    <div class="logo">
        <img src="/img/logo-removebg-preview.png" alt="Logo"> 
    </div>
    <div class="navigation">
        <a href="principal.php" class="active"><i class="fas fa-home"></i> Inicio</a>
        <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
        <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
        <a href="cerrar_sesion.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </div>
</header>

<div class="container">
    <div class="titulo-container">
        <h1>
            <?php 
            if (isset($partida['titulo']) && !empty($partida['titulo'])) {
                echo htmlspecialchars($partida['titulo']);
            } else {
                echo "Partida #" . htmlspecialchars($partida['idPartida']);
            }
            ?>
            <?php if ($esCoach): ?>
                <button class="btn-editar-titulo" id="btnEditarTitulo"><i class="fas fa-edit"></i></button>
            <?php endif; ?>
        </h1>
            
        <?php if ($esCoach): ?>
            <div id="formEditarTitulo" class="form-editar-titulo" style="display: none;">
                <form method="POST" action="actualizar_titulo.php">
                    <input type="hidden" name="idPartida" value="<?php echo $idPartida; ?>">
                    <input type="text" name="nuevo_titulo" value="<?php echo isset($partida['titulo']) ? htmlspecialchars($partida['titulo']) : ''; ?>" required>
                    <button type="submit">Guardar</button>
                    <button type="button" id="btnCancelarEdicion">Cancelar</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="partido-detalles">
        <h2>Información del Partido</h2>
        
        <?php if(isset($partida['mapa'])): ?>
        <div class="mapa-container">
            <ul class="info-partida">
                <?php if(isset($partida['fecha'])): ?>
                    <li><strong>Fecha:</strong> <?php echo htmlspecialchars($partida['fecha']); ?></li>
                <?php endif; ?>
                
                <?php if(isset($partida['equipo_local']) && isset($partida['equipo_visitante'])): ?>
                    <li><strong>Equipos:</strong> <?php echo htmlspecialchars($partida['equipo_local']); ?> vs <?php echo htmlspecialchars($partida['equipo_visitante']); ?></li>
                <?php endif; ?>
                
                <?php if(isset($partida['resultado'])): ?>
                    <li><strong>Resultado:</strong> <?php echo htmlspecialchars($partida['resultado']); ?></li>
                <?php endif; ?>
                
                <?php if(isset($partida['puntuacion_local']) && isset($partida['puntuacion_visitante'])): ?>
                    <li><strong>Puntuación:</strong> <?php echo htmlspecialchars($partida['puntuacion_local']); ?> - <?php echo htmlspecialchars($partida['puntuacion_visitante']); ?></li>
                <?php endif; ?>
                
                <?php 
                foreach($partida as $campo => $valor):
                    $camposExcluidos = ['idPartida', 'titulo', 'descripcion', 'fecha', 'equipo_local', 'equipo_visitante', 'resultado', 'mapa', 'puntuacion_local', 'puntuacion_visitante'];
                    if(!in_array($campo, $camposExcluidos) && !empty($valor)):
                ?>
                    <li><strong><?php echo ucfirst(str_replace('_', ' ', $campo)); ?>:</strong> <?php echo htmlspecialchars($valor); ?></li>
                <?php 
                    endif;
                endforeach; 
                ?>
            </ul>
             <!-- Nuevo contenedor para la gráfica -->
    <div class="grafica-container">
        <canvas id="estadisticasPartido"></canvas>
    </div>
            <?php
            // Normalizar el nombre del mapa para buscar la imagen
            $mapaLower = strtolower(str_replace(' ', '_', trim($partida['mapa'])));
            
           // Mapeo de nombres alternativos si es necesario
$mapaMapping = [
    'dust_ii' => 'dust2',
    'dust_2' => 'dust2',
    'd2' => 'dust2',
    // Añade aquí otros mapeos si es necesario
];
            
            // Verificar si hay un mapeo alternativo
            if (isset($mapaMapping[$mapaLower])) {
                $mapaLower = $mapaMapping[$mapaLower];
            }
            
            // Ruta de la imagen con extensión .jfif
            $rutaImagen = "img/mapas/{$mapaLower}.jfif";
            
            // Si no se encuentra la imagen, usar la imagen predeterminada
            if (!file_exists($rutaImagen)) {
                $rutaImagen = "img/mapas/default.jfif";
                
                // Si tampoco existe la imagen predeterminada, usar un placeholder
                if (!file_exists($rutaImagen)) {
                    $rutaImagen = "https://via.placeholder.com/300x200?text=Mapa+no+disponible";
                }
            }
            ?>
            
            <div class="mapa-imagen">
                <img src="<?php echo $rutaImagen; ?>" alt="Mapa <?php echo htmlspecialchars($partida['mapa']); ?>">
                <div class="mapa-nombre"><?php echo htmlspecialchars($partida['mapa']); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="videos">
        <h2>Videos de la Partida</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <?php if ($esCoach): ?>
        <form method="POST">
            <input type="text" name="titulo" placeholder="Título del video" required>
            <input type="text" name="codigo_youtube" placeholder="Código de YouTube" required>
            <button type="submit">Agregar Video</button>
        </form>
        <?php endif; ?>

        <ul class="videos-list">
            <?php foreach ($videos as $video): ?>
                <li>
                    <h3><?php echo htmlspecialchars($video['titulo']); ?></h3>
                    <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['codigo_youtube']); ?>" allowfullscreen></iframe>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="apuntes-coach">
        <h2>Apuntes del Coach</h2>
        <?php if ($mensajeApuntes): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensajeApuntes); ?></div>
        <?php endif; ?>
        
        <?php if ($esCoach): ?>
        <form method="POST" class="coach-form">
            <textarea name="apuntes" rows="5" placeholder="Escribe tus apuntes aquí..." required><?php echo htmlspecialchars($apuntes); ?></textarea>
            <button type="submit">Guardar Apuntes</button>
        </form>
        <?php endif; ?>

        <?php if (!empty($apuntes)): ?>
        <div class="coach-notes-display">
            <div class="coach-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="notes-content">
                <div class="notes-header">
                    <h3>Análisis del Coach</h3>
                    <?php if(isset($apuntesExistentes['fecha_modificacion'])): ?>
                    <span class="notes-date">Última actualización: <?php echo date('d/m/Y H:i', strtotime($apuntesExistentes['fecha_modificacion'])); ?></span>
                    <?php endif; ?>
                </div>
                <div class="notes-body">
                    <?php 
                    $parrafos = explode("\n", $apuntes);
                    foreach ($parrafos as $parrafo):
                        if (trim($parrafo) !== ''): 
                    ?>
                        <p><?php echo htmlspecialchars($parrafo); ?></p>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="no-notes">
            <i class="fas fa-info-circle"></i> No hay apuntes disponibles del coach para esta partida.
        </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnEditarTitulo = document.getElementById('btnEditarTitulo');
    const formEditarTitulo = document.getElementById('formEditarTitulo');
    const btnCancelarEdicion = document.getElementById('btnCancelarEdicion');
    
    if (btnEditarTitulo) {
        btnEditarTitulo.addEventListener('click', function() {
            formEditarTitulo.style.display = 'block';
        });
    }

    if (btnCancelarEdicion) {
        btnCancelarEdicion.addEventListener('click', function() {
            formEditarTitulo.style.display = 'none';
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Código existente para el botón de editar título
    
    // Nuevo código para la gráfica
    const ctx = document.getElementById('estadisticasPartido').getContext('2d');
    
    // Obtener datos del partido desde PHP
    const puntuacionLocal = <?php echo isset($partida['puntuacion_local']) ? intval($partida['puntuacion_local']) : 0; ?>;
    const puntuacionVisitante = <?php echo isset($partida['puntuacion_visitante']) ? intval($partida['puntuacion_visitante']) : 0; ?>;
    const equipoLocal = '<?php echo isset($partida['equipo_local']) ? htmlspecialchars($partida['equipo_local']) : "Equipo Local"; ?>';
    const equipoVisitante = '<?php echo isset($partida['equipo_visitante']) ? htmlspecialchars($partida['equipo_visitante']) : "Equipo Visitante"; ?>';
    
    // Crear datos simulados para estadísticas más detalladas
    // Estos podrían venir de tu base de datos en una implementación real
    const estadisticasLocal = {
        kills: Math.floor(Math.random() * 50) + 30,
        muertes: Math.floor(Math.random() * 40) + 20,
        asistencias: Math.floor(Math.random() * 20) + 10,
        headshots: Math.floor(Math.random() * 30) + 15,
        flashbangs : Math.floor(Math.random() * 10) + 5
    };

    const estadisticasVisitante = {
        kills: Math.floor(Math.random() * 50) + 30,
        muertes: Math.floor(Math.random() * 40) + 20,
        asistencias: Math.floor(Math.random() * 20) + 10,
        headshots: Math.floor(Math.random() * 30) + 15,
        flashbangs: Math.floor(Math.random() * 10) + 5
    };

    const data = {
        labels: ['Kills', 'Muertes', 'Asistencias', 'Headshots', 'Flashbangs'],
        datasets: [
            {
                label: equipoLocal,
                data: [estadisticasLocal.kills, estadisticasLocal.muertes, estadisticasLocal.asistencias, estadisticasLocal.headshots, estadisticasLocal.flashbangs],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            },
            {
                label: equipoVisitante,
                data: [estadisticasVisitante.kills, estadisticasVisitante.muertes, estadisticasVisitante.asistencias, estadisticasVisitante.headshots, estadisticasVisitante.flashbangs],
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }
        ]
    };

    const config = {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    };

    const estadisticasChart = new Chart(ctx, config);
});
</script>
</body>
</html>