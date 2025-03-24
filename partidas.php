<?php
session_start();

if (!isset($_SESSION["idUsuario"])) {
    header("Location: index.php"); 
    exit();
}

require_once "config.php"; 

$pdo = conectarDB();
$idUsuario = $_SESSION["idUsuario"];
$esCoach = isset($_SESSION["role"]) && $_SESSION["role"] === 'coach';

try {
    $stmt = $pdo->prepare("SELECT * FROM Partida WHERE idUsuario = :idUsuario ORDER BY fecha DESC");
    $stmt->execute(["idUsuario" => $idUsuario]);
    $partidas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener las partidas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Partidas - CStats</title>
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
            --win-color: #4CAF50;
            --loss-color: #f44336;
            --draw-color: #2196F3;
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

        .partidas-container {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .partida-card {
            background-color: rgba(40, 40, 40, 0.85);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }

        .partida-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.4);
        }

        .partida-header {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            margin-bottom: 15px;
        }

        .partida-header h2 {
            margin: 0;
            color: var(--primary-color);
        }

        .partida-info {
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 15px;
            background-color: var(--accent-color);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            transition: background-color var(--transition-speed) ease;
        }

        .btn:hover {
            background-color: var(--accent-hover);
        }

        footer {
            background-color: #1e1e1e;
            color: var(--text-light);
            text-align: center;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>
    <nav class="navigation">
    <div class="navigation">
                    <a href="principal.php" class="active"><i class="fas fa-home"></i> Inicio</a>
                    <a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a>
                    <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
                    <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
                    <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>

                </div>
    </nav>
</header>

<main class="content">
    <h1>Mis Partidas</h1>

    <div class="button-group">
        <a href="registrar_partida.php" class="btn">Registrar Nueva Partida</a>
    </div>

    <div class="partidas-container">
        <?php if (count($partidas) > 0): ?>
            <?php foreach ($partidas as $partida): ?>
                <div class="partida-card">
                    <div class="partida-header">
                        <h2>Partida #<?php echo htmlspecialchars($partida["idPartida"]); ?></h2>
                        <span><?php echo htmlspecialchars($partida["fecha"]); ?></span>
                    </div>
                    <div class="partida-info">
                        <p><strong>Mapa:</strong> <?php echo htmlspecialchars($partida["mapa"]); ?></p>
                        <p><strong>Resultado:</strong> <?php echo htmlspecialchars($partida["resultado"]); ?></p>
                    </div>
                    <a href="ver_partida.php?id=<?php echo htmlspecialchars($partida["idPartida"]); ?>" class="btn">Ver Detalles</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes partidas registradas aún.</p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Tu Proyecto. Todos los derechos reservados.</p>
</footer>

</body>
</html>

<?php
$rolUsuario = $_SESSION["rolUsuario"] ?? 'usuario'; 

if ($rolUsuario === 'coach') {
}