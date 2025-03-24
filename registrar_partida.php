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

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"] ?? "");
    $mapa = trim($_POST["mapa"] ?? "");
    $fecha = trim($_POST["fecha"] ?? date("Y-m-d"));
    $resultado = trim($_POST["resultado"] ?? "");
    
    if (empty($titulo) || empty($mapa) || empty($fecha)) {
        $error = "Por favor, completa todos los campos requeridos.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO Partida (idUsuario, titulo, mapa, fecha, resultado) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$idUsuario, $titulo, $mapa, $fecha, $resultado]);
            
            $mensaje = "Partida registrada correctamente.";
            
            header("Location: partidas.php?mensaje=Partida registrada correctamente");
            exit();
        } catch (PDOException $e) {
            $error = "Error al registrar la partida: " . $e->getMessage();
        }
    }
}

$mapas = [
    "Ancient", "Anubis", "Dust II", "Inferno", "Mirage", "Nuke", "Overpass", "Vertigo"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Partida - CStats</title>
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
            padding: 20px;
        }

        .form-container {
            background-color: rgba(40, 40, 40, 0.85);
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: #333;
            color: var(--text-light);
        }

        .btn {
            background-color: var(--accent-color);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color var(--transition-speed) ease;
        }

        .btn:hover {
            background-color: var(--accent-hover);
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Registrar Nueva Partida</h1>
    <?php if ($mensaje): ?>
        <p style="color: green;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>
        <div class="form-group">
            <label for="mapa">Mapa:</label>
            <select id="mapa" name="mapa" required>
                <?php foreach ($mapas as $mapa): ?>
                    <option value="<?php echo htmlspecialchars($mapa); ?>"><?php echo htmlspecialchars($mapa); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="fecha">Fecha:</label>
            <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label for="resultado">Resultado:</label>
            <input type="text" id="resultado" name="resultado">
        </div>
        <button type="submit" class="btn">Registrar Partida</button>
    </form>
</div>

</body>
</html>