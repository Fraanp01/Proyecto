
Run
Copy code
<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Incluir archivo de configuración de base de datos
require_once("config.php");

try {
    // Establecer conexión a la base de datos
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $dbname = "bkwgpnt7d5hd7bpuiwbw";
    $usuario = "uq6vff78pyt2g5lo";
    $contrasena = "u6l50PObWFQEFcpTIp5a";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Primero, vamos a verificar la estructura de la tabla para descubrir los nombres de columna correctos
    $stmt = $pdo->prepare("DESCRIBE Estadisticas");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Si encontramos una columna similar a 'resultado', usaremos ese nombre
    $columnaResultado = null;
    foreach ($columns as $column) {
        if (stripos($column, 'result') !== false || stripos($column, 'estado') !== false || stripos($column, 'victoria') !== false) {
            $columnaResultado = $column;
            break;
        }
    }
    
    // Ahora modificamos la consulta SQL basada en lo que encontramos
    if ($columnaResultado) {
        $stmt = $pdo->prepare("SELECT 
            SUM(kills) as totalKills, 
            SUM(muertes) as totalMuertes,
            SUM(CASE WHEN $columnaResultado = 'victoria' THEN 1 ELSE 0 END) as victorias,
            SUM(CASE WHEN $columnaResultado = 'derrota' THEN 1 ELSE 0 END) as derrotas
            FROM Estadisticas WHERE idUsuario = :idUsuario");
    } else {
        // Si no hay columna de resultado, hacemos una consulta más simple
        $stmt = $pdo->prepare("SELECT 
            SUM(kills) as totalKills, 
            SUM(muertes) as totalMuertes
            FROM Estadisticas WHERE idUsuario = :idUsuario");
    }
    
    $stmt->execute(["idUsuario" => $_SESSION["idUsuario"]]);
    $estadisticas = $stmt->fetch();
    
    // Si no hay columna de resultado, establecemos valores predeterminados para victorias y derrotas
    if (!isset($estadisticas['victorias'])) {
        $estadisticas['victorias'] = 0;
        $estadisticas['derrotas'] = 0;
    }
    
    // Si no hay estadísticas, establecer valores predeterminados
    if (!$estadisticas['totalKills'] && !$estadisticas['totalMuertes']) {
        $estadisticas = [
            'totalKills' => 0,
            'totalMuertes' => 0,
            'victorias' => 0,
            'derrotas' => 0
        ];
    }
} catch (PDOException $e) {
    // Registrar el error y mostrar un mensaje genérico
    error_log("Error en estadisticas.php: " . $e-> getMessage());
    $error = "Error de conexión: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - CS2 Stats Tracker</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo-removebg-preview.png" alt="CStats Logo" />
        </div>
        <nav class="navigation">
            <a href="dashboard.php">Dashboard</a>
            <a href="partidas.php">Partidas</a>
            <a href="estadisticas.php" class="active">Estadísticas</a>
            <a href="estrategias.php">Estrategias</a>
            <a href="feedback.php">Feedback</a>
            <a href="logout.php">Cerrar sesión</a>
        </nav>
    </header>
    
    <div class="content" id="estadisticas">
        <section class="section">
            <h2>Estadísticas Generales</h2>
            
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php elseif (isset($estadisticas)): ?>
                <div class="stats">
                    <div class="stat">
                        <h3>K/D Ratio</h3>
                        <p>
                            <?php 
                            if ($estadisticas["totalMuertes"] > 0) {
                                echo round($estadisticas["totalKills"] / $estadisticas["totalMuertes"], 2);
                            } else {
                                echo "N/A";
                            }
                            ?>
                        </p>
                    </div>
                    <div class="stat">
                        <h3>Victorias</h3>
                        <p><?php echo $estadisticas["victorias"]; ?></p> 
                    </div>
                    <div class="stat">
                        <h3>Derrotas</h3>
                        <p><?php echo $estadisticas["derrotas"]; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
    
    <footer>
        <p>&copy; 2025 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html> 