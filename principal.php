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
        // Verificar si ya existen ap untes para la partida
        $stmt = $pdo->prepare("SELECT * FROM ApuntesCoach WHERE idPartida = ?");
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
                <a href="principal.php" class="active"><i class="fas fa-home"></i> Inicio</a>
                <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
                <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
            </div>
        </div>
        <div class="nav-right">
            <a href="cerrar_sesion.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>
    </div>
</header>

<div class="container">
    <h1><?php echo isset($partida['titulo']) ? htmlspecialchars($partida['titulo']) : 'Título no disponible'; ?></h1>
    
    <div class="partido-detalles">
        <h2>Información del Partido</h2>
        <ul class="info-partida">
            <?php if(isset($partida['fecha'])): ?>
                <li><strong>Fecha:</strong> <?php echo htmlspecialchars($partida['fecha']); ?></li>
            <?php endif; ?>
            
            <?php if(isset ($partida['equipo_local']) && isset($partida['equipo_visitante'])): ?>
                <li><strong>Equipos:</strong> <?php echo htmlspecialchars($partida['equipo_local']); ?> vs <?php echo htmlspecialchars($partida['equipo_visitante']); ?></li>
            <?php endif; ?>
            
            <?php if(isset($partida['resultado'])): ?>
                <li><strong> Resultado:</strong> <?php echo htmlspecialchars($partida['resultado']); ?></li>
            <?php endif; ?>
            
            <?php if(isset($partida['mapa'])): ?>
                <li><strong>Mapa:</strong> <?php echo htmlspecialchars($partida['mapa']); ?></li>
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
    </div>

    <div class="videos">
        <h2>Videos de la Partida</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="titulo" placeholder="Título del video" required>
            <input type="text" name="codigo_youtube" placeholder="Código de YouTube" required>
            <button type="submit">Agregar Video</button>
        </form>

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
        
        <form method="POST">
            <textarea name="apuntes" rows="5" placeholder="Escribe tus apuntes aquí..." required><?php echo htmlspecialchars($apuntes); ?></textarea>
            <button type="submit">Guardar Apuntes</button>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
</footer>

</body>
</html>