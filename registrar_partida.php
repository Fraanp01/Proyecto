<?php
session_start(); 

if (!isset($_SESSION["idUsuario"])) {
    die("ERROR: No se encontró el ID de usuario en la sesión.");
}

$idUsuario = $_SESSION["idUsuario"]; 


function conectarDB() {
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $database = "bkwgpnt7d5hd7bpuiwbw";
    $user = "uq6vff78pyt2g5lo";
    $pass = "u6l50PObWFQEFcpTIp5a";

    try {
        return new PDO("mysql:host=$host;dbname=$database", $user, $pass);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}


function guardarPartido($idUsuario, $mapa, $fecha, $resultado) {
    $pdo = conectarDB();
    if ($pdo != null) {
        $consulta = "INSERT INTO Partida (idUsuario, mapa, fecha, resultado) 
                     VALUES (:paramIdUsuario, :paramMapa, :paramFecha, :paramResultado)";
        $resul = $pdo->prepare($consulta);

        if ($resul != null) {
            $resul->execute([
                "paramIdUsuario" => $idUsuario,
                "paramMapa" => $mapa,
                "paramFecha" => $fecha,
                "paramResultado" => $resultado
            ]);
            
            header("Location: partidas.php");
            exit();
        } else {
            echo "<p class='error'>ERROR al registrar el partido.</p>";
        }
    }
}


if (isset($_POST['mapa']) && isset($_POST['fecha']) && isset($_POST['resultado'])) {
    $mapa = $_POST['mapa']; 
    $fecha = $_POST['fecha']; 
    $resultado = $_POST['resultado']; 
    guardarPartido($idUsuario, $mapa, $fecha, $resultado); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Partido</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <div class="logo">
        <img src="logo.png" alt="Logo">
    </div>
    <nav class="navigation">
        <a href="index.php">Inicio</a>
        <a href="registrar_partida.php">Registrar Partida</a>
        <a href="estadisticas.php">Estadísticas</a>
    </nav>
</header>

<main class="content">
    <h1>Registrar Partido</h1>

    
    <form method="post" action="" class="formulario">
        <div class="input-group">
            <label for="mapa">Nombre del Mapa:</label>
            <input type="text" name="mapa" id="mapa" required>
        </div>

        <div class="input-group">
            <label for="fecha">Fecha del partido:</label>
            <input type="date" name="fecha" id="fecha" required>
        </div>

        <div class="input-group">
            <label for="resultado">Resultado del partido:</label>
            <input type="text" name="resultado" id="resultado" required>
        </div>
        
        <div class="button-group">
            <button type="submit" class="btn login-btn">Registrar Partido</button>
        </div>
    </form>
</main>

<footer>
    <p>&copy; 2025 Tu Proyecto. Todos los derechos reservados.</p>
</footer>

</body>
</html>
