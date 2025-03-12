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
    $stmt-> execute([$idPartida]);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si hay un error, continuamos sin videos
}

// Obtener estadísticas de la partida
$estadisticas = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM Estadisticas WHERE idPartida = ?");
    $stmt->execute([$idPartida]);
    $estadisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si hay un error, continuamos sin estadísticas
}

// Obtener datos específicos de la partida
$datosPartida = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM DatosPartida WHERE idPartida = ?");
    $stmt->execute([$idPartida]);
    $datosPartida = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si hay un error, continuamos sin datos de la partida
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Partida - CStats</title>
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
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
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

        h2 {
            color: var(--primary-color);
            margin-top: 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 20px;
            background-color: rgba(40, 40, 40, 0.85);
            border-radius: 10px;
            padding: 15px;
            box-shadow: var(--box-shadow);
        }

        iframe {
            width: 100%;
            height: 315px;
            border: none;
            border-radius: 10px;
        }

        .btn {
            padding: 10px 15px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color var(--transition-speed) ease;
            display: inline-block;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--accent-hover);
        }

        .error {
            color: #f44336;
        }

        .success {
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo isset($partida['titulo']) ? htmlspecialchars($partida['titulo']) : 'Detalles de la Partida'; ?></h1>
        <?php if(isset($partida['descripcion']) && !empty($partida['descripcion'])): ?>
            <p><?php echo htmlspecialchars($partida['descripcion']); ?></p>
        <?php endif; ?>

        <h2>Videos</h2>
        <ul>
            <?php foreach ($videos as $video): ?>
                <li>
                    <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video['codigo_youtube']); ?>" allowfullscreen></iframe>
                    <p><?php echo htmlspecialchars($video['titulo']); ?></p>
 </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($esCoach): ?>
            <h2>Añadir Video de YouTube</h2>
            <form method="POST">
                <label for="titulo">Título:</label>
                <input type="text" name="titulo" required>
                <label for="codigo_youtube">Código o URL de YouTube:</label>
                <input type="text" name="codigo_youtube" required>
                <button type="submit" class="btn">Añadir Video</button>
            </form>
            <?php if ($mensaje): ?>
                <p class="success"><?php echo $mensaje; ?></p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <h2>Estadísticas de la Partida</h2>
        <ul>
            <?php foreach ($estadisticas as $stat): ?>
                <li>
                    <strong><?php echo htmlspecialchars($stat['nombre_estadistica']); ?>:</strong> <?php echo htmlspecialchars($stat['valor']); ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2>Datos de la Partida</h2>
        <ul>
            <?php foreach ($datosPartida as $dato): ?>
                <li>
                    <strong><?php echo htmlspecialchars($dato['nombre_dato']); ?>:</strong> <?php echo htmlspecialchars($dato['valor']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>