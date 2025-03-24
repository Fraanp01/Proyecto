<?php
session_start();

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['idUsuario'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config.php';
    if (!function_exists('conectarDB')) {
        require_once 'funciones.php';
    }
    
    $conexion = conectarDB();
    
    $usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? null;
    
    $es_coach = false;
    
    try {
        $stmt = $conexion->prepare("SELECT role FROM login WHERE idUsuario = :id");
        $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && $usuario['role'] === 'coach') {
            $es_coach = true;
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje_error'] = "Error al verificar el rol del usuario: " . $e->getMessage();
        header('Location: perfil.php');
        exit;
    }
    
    if (!$es_coach) {
        $_SESSION['mensaje_error'] = "Solo los coaches pueden agregar entrenamientos.";
        header('Location: perfil.php');
        exit;
    }
    
    $usuario_destino_id = $_POST['usuario_id'];
    $titulo = $_POST['titulo'];
    $fecha = $_POST['fecha'];
    $duracion = $_POST['duracion'];
    $descripcion = $_POST['descripcion'];
    
    if (empty($usuario_destino_id) || empty($titulo) || empty($fecha) || empty($duracion) || empty($descripcion)) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header('Location: perfil.php');
        exit;
    }
    
    try {
        $stmt = $conexion->prepare("SHOW TABLES LIKE 'entrenamientos'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE entrenamientos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                titulo VARCHAR(255) NOT NULL,
                fecha DATETIME NOT NULL,
                duracion VARCHAR(50) NOT NULL,
                descripcion TEXT NOT NULL,
                fecha_ creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES login(idUsuario) ON DELETE CASCADE
            )";
            $conexion->exec($sql);
        }

        $stmt = $conexion->prepare("INSERT INTO entrenamientos (usuario_id, titulo, fecha, duracion, descripcion) VALUES (:usuario_id, :titulo, :fecha, :duracion, :descripcion)");
        $stmt->bindParam(':usuario_id', $usuario_destino_id, PDO::PARAM_INT);
        $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':duracion', $duracion, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->execute();

        $_SESSION['mensaje_exito'] = "Entrenamiento agregado exitosamente.";
    } catch (PDOException $e) {
        $_SESSION['mensaje_error'] = "Error al agregar el entrenamiento: " . $e->getMessage();
    }

    header('Location: perfil.php');
    exit;
}
?>