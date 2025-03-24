<?php
session_start();

if (!isset($_SESSION['usuario_id']) && isset($_SESSION['idUsuario'])) {
    $_SESSION['usuario_id'] = $_SESSION['idUsuario'];
}

if (!isset($_SESSION['nombre']) && isset($_SESSION['username'])) {
    $_SESSION['nombre'] = $_SESSION['username'];
}

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['idUsuario']) && !isset($_SESSION['username'])) {
    $_SESSION['redirect_after_login'] = 'perfil.php';
    header('Location: index.php'); 
    exit;
}

$nombre_usuario = $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario';
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? null;

$usuario = [
    'nombre' => $nombre_usuario,
    'email' => 'usuario@ejemplo.com',
    'fecha_registro' => date('Y-m-d'),
    'role' => 'usuario'
];

$preferencias = [
    'tema' => 'oscuro',
    'notificaciones' => 'activadas',
    'privacidad' => 'amigos'
];

$actividad_reciente = [
    ['tipo' => 'login', 'fecha' => date('Y-m-d H:i:s', strtotime('-1 day'))],
    ['tipo' => 'cambio de contraseña', 'fecha' => date('Y-m-d H:i:s', strtotime('-3 days'))],
    ['tipo' => 'actualización de perfil', 'fecha' => date('Y-m-d H:i:s', strtotime('-5 days'))],
];

$entrenamientos = [];

$es_coach = false;
$usuarios_lista = [];

$db_connected = false;
$connection_error = '';

if (file_exists('config.php') && file_exists('funciones.php')) {
    try {
        require_once 'config.php';
        
        if (!function_exists('conectarDB')) {
            require_once 'funciones.php';
        }
        
        if (function_exists('conectarDB')) {
            $conexion = conectarDB();
            
            $db_connected = true;
            
            if ($usuario_id !== null) {
                $stmt = $conexion->prepare("SELECT * FROM login WHERE idUsuario = :id");
                $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario_db) {
                    $usuario['nombre'] = $usuario_db['user'] ?? $usuario['nombre'];
                    $usuario['email'] = $usuario_db['email'] ?? $usuario['email'];
                    $usuario['fecha_registro'] = $usuario_db['fecha_registro'] ?? $usuario['fecha_registro'];
                    $usuario['role'] = $usuario_db['role'] ?? $usuario['role'];
                    
                    if ($usuario['role'] === 'coach') {
                        $es_coach = true;
                    }
                }
            }

            if ($usuario_id !== null) {
                $stmt = $conexion->prepare("SELECT * FROM entrenamientos WHERE usuario_id = :usuario_id");
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                $entrenamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            if ($es_coach) {
                $stmt = $conexion->prepare("SELECT idUsuario, user FROM login WHERE role = 'usuario' ORDER BY user ASC");
                $stmt->execute();
                $usuarios_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        $connection_error = 'Error de conexión: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | CStats</title>
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
            background-image: url('img/cs21.jpeg'); 
            background-size: cover; 
            background-position: center; 
            background-attachment: fixed; 
            position: relative;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
            color: var(--text-light);
        }
        
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: -1;
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
            padding-left: 300px;
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
            margin-right: auto; 
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

 .btn-logout {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all var(--transition-speed) ease;
        }

        .btn-logout:hover {
            background-color: rgba(255, 202, 40, 0.1);
            color: var(--primary-color);
        }

        .perfil-container {
            max-width: 800px;
            margin: 100px auto 20px; 
            padding: 20px;
            background: rgba(40, 40, 40, 0.85);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
        }
        h1, h2 {
            color: var(--primary-color);
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
        .perfil-info, .preferencias, .actividad-reciente, .entrenamientos {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: var(--background-medium);
            color: var(--text-light);
            position: relative;
            bottom: 0;
            width: 100%;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: var(--primary-color);
        }

        input[type="text"],
        input[type="datetime-local"],
        textarea,
        select {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.2);
            color: var(--text-light);
            transition: border-color var(--transition-speed);
        }

        input[type="text"]:focus,
        input[type="datetime-local"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        button.btn-agregar {
            background-color: var(--primary-color);
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color var(--transition-speed);
        }

        button.btn-agregar:hover {
            background-color: var(--accent-hover);
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="nav-left">
                <div class="navigation">
                    <a href="principal.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a>
                    <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
                    <a href="estrategias.php"><i class="fas fa-lightbulb"></i> Estrategias</a>
                    <a href="perfil.php" class="active"><i class="fas fa-user"></i> Perfil</a>
                    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <div class="perfil-container">
        <h1>Perfil de <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
        <?php if ($connection_error): ?>
            <div class="error-message">
                <p><?php echo htmlspecialchars($connection_error); ?></p>
            </div>
        <?php endif; ?>
        <div class="perfil-info">
            <h2>Información del Usuario</h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Fecha de Registro:</strong> <?php echo htmlspecialchars($usuario['fecha_registro']); ?></p>
            <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['role']); ?></p>
        </div>

        <?php if ($es_coach): ?>
            <h2>Agregar Entrenamiento</h2>
            <form action="agregar_entrenamiento.php" method="POST">
                <label for="usuario">Seleccionar Usuario:</label>
                <select name="usuario_id" id="usuario" required>
                    <?php foreach ($usuarios_lista as $usuario): ?>
                        <option value="<?php echo htmlspecialchars($usuario['idUsuario']); ?>"><?php echo htmlspecialchars($usuario['user']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="titulo">Título del Entrenamiento:</label>
                <input type="text" name="titulo" id="titulo" required>

                <label for="fecha">Fecha y Hora:</label>
                <input type="datetime-local" name="fecha" id="fecha" required>

                <label for="duracion">Duración:</label>
                <input type="text" name="duracion" id="duracion" required>

                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" required></textarea>

                <button type="submit" class="btn-agregar">Agregar Entrenamiento</button>
            </form>
        <?php endif; ?>

        <h2>Entrenamientos Programados</h2>
        <div class="entrenamientos">
            <?php if (empty($entrenamientos)): ?>
                <p>No hay entrenamientos programados.</p>
            <?php else: ?>
                <?php foreach ($entrenamientos as $entrenamiento): ?>
                    <div class="entrenamiento">
                        <h3><?php echo htmlspecialchars($entrenamiento['titulo']); ?></h3>
                        <p><strong>Fecha y Hora:</strong> <?php echo htmlspecialchars($entrenamiento['fecha']); ?></p>
                        <p><strong>Duración:</strong> <?php echo htmlspecialchars($entrenamiento['duracion']); ?></p>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($entrenamiento['descripcion']); ?></p>
                        <?php if ($es_coach): ?>
                            <form action="eliminar_entrenamiento.php" method="POST" style="display:inline;">
                                <input type="hidden" name="entrenamiento_id" value="<?php echo htmlspecialchars($entrenamiento['id']); ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="preferencias">
            <h2>Preferencias</h2>
            <p><strong>Tema:</strong> <?php echo htmlspecialchars($preferencias['tema']); ?></p>
            <p><strong>Notificaciones:</strong> <?php echo htmlspecialchars($preferencias['notificaciones']); ?></p>
            <p><strong>Privacidad:</strong> <?php echo htmlspecialchars($preferencias['privacidad']); ?></p>
        </div>

        <div class="actividad-reciente">
            <h2>Actividad Reciente</h2>
            <ul>
                <?php foreach ($actividad_reciente as $actividad): ?>
                    <li><?php echo htmlspecialchars($actividad['tipo']); ?> - <?php echo date('d/m/Y H:i', strtotime($actividad['fecha'])); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <footer>
        <p>&copy; 2023 C Stats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entrenamiento_id'])) {
    $entrenamiento_id = $_POST['entrenamiento_id'];

    try {
        $stmt = $conexion->prepare("DELETE FROM entrenamientos WHERE id = :id");
        $stmt->bindParam(':id', $entrenamiento_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['mensaje_entrenamiento'] = "Entrenamiento eliminado exitosamente.";
    } catch (PDOException $e) {
        $_SESSION['mensaje_entrenamiento'] = "Error al eliminar el entrenamiento: " . $e->getMessage();
    }

    header('Location: perfil.php');
    exit;
}
?>