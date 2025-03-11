<?php
session_start();


if (!isset($_SESSION["username"])) {
    header("Location: index.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal - CStats</title>
    <link rel="stylesheet" href="styles.css"> 
    <style>
        
        body {
            background-image: url('/img/cs21.jpeg'); 
            background-size: cover; 
            background-position: center; 
            background-attachment: fixed; 
            background-color: rgba(0, 0, 0, 0.5); 
        }

        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); 
            z-index: -1; 
        }

        
        .content {
            position: relative;
            z-index: 1; 
            color: #fff; 
            padding: 20px;
        }

        
        .section {
            background-color: rgba(0, 0, 0, 0.7); 
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .section h2 {
            color: #ffca28; 
            margin-bottom: 15px;
        }

        
        nav {
            background-color: #1c1c1c; 
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        nav a {
            color: #e0e0e0; 
            text-decoration: none;
            margin: 0 10px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #ff5722; 
        }

        
        .btn-logout {
            padding: 10px 20px;
            background-color: #ff5722; 
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #ff784e; 
        }
    </style>
</head>
<body>
    
    <div class="overlay"></div>

    
    <nav>
        <div class="logo">
            <img src="img/logo.png" alt="Logo" height="50"> 
        </div>
        <div class="navigation">
            <a href="principal.php">Inicio</a>
            <a href="estadisticas.php">Estadísticas</a>
            <a href="partidas.php">Partidas</a>
            <a href="estrategias.php">Estrategias</a>
            <a href="feedback.php">Feedback</a>
        </div>
        <a href="logout.php" class="btn-logout">Cerrar sesión</a>
    </nav>

    
    <div class="content">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>

        
        <div class="section">
            <h2>Estadísticas</h2>
            <p>Aquí puedes ver un resumen de tus estadísticas recientes.</p>
            <a href="estadisticas.php" class="btn-logout">Ver más</a>
        </div>

        
        <div class="section">
            <h2>Partidas</h2>
            <p>Revisa tus partidas recientes y analiza tu rendimiento.</p>
            <a href="partidas.php" class="btn-logout">Ver más</a>
        </div>

        
        <div class="section">
            <h2>Estrategias</h2>
            <p>Explora estrategias recomendadas para mejorar tu juego.</p>
            <a href="estrategias.php" class="btn-logout">Ver más</a>
        </div>

        
        <div class="section">
            <h2>Feedback</h2>
            <p>Envía tus comentarios y sugerencias para mejorar la aplicación.</p>
            <a href="feedback.php" class="btn-logout">Enviar Feedback</a>
        </div>
    </div>
</body>
</html>