<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $video = $_FILES['video'];

    // Validar el archivo de video
    $allowedTypes = ['video/mp4', 'video/avi', 'video/mkv'];
    if (in_array($video['type'], $allowedTypes) && $video['error'] == 0) {
        $videoPath = 'uploads/videos/' . uniqid() . '-' . basename($video['name']);
        if (move_uploaded_file($video['tmp_name'], $videoPath)) {
            try {
                $pdo = conectarDB();
                $stmt = $pdo->prepare("INSERT INTO Videos (titulo, descripcion, ruta_video, idUsuario) VALUES (:titulo, : descripcion, :ruta_video, :idUsuario)");
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'ruta_video' => $videoPath,
                    'idUsuario' => $_SESSION["idUsuario"]
                ]);
                $mensaje = "Video subido exitosamente.";
            } catch (PDOException $e) {
                die("Error al subir el video: " . $e->getMessage());
            }
        } else {
            $mensaje = "Error al mover el archivo subido.";
        }
    } else {
        $mensaje = "Error al subir el video. Asegúrate de que el archivo sea un video válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Video - CStats</title>
    <link rel="stylesheet" href="css.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="nav-left">
                <div class="logo">
                    <img src="img/logo.png" alt="Logo CStats">
                </div>
                <nav>
                    <a href="index.php">Inicio</a>
                    <a href="partidas.php">Mis Partidas</a>
                    <a href="ver_partida.php">Ver Partidas</a>
                </nav>
            </div>
            <div class="nav-right">
                <a href="logout.php">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="subir-video-container">
        <h2>Subir Video</h2>
        <?php if (isset($mensaje)): ?>
            <p><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <form action="subir_video.php" method="post" enctype="multipart/form-data">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" required>
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" required></textarea>
            <label for="video">Selecciona un video:</label>
            <input type="file" name="video" accept="video/*" required>
            <button type="submit">Subir Video</button>
        </form>
    </div>
</body>
</html>