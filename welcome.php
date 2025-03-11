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
    <title>Bienvenido</title>
    <link rel="stylesheet" href="welcomecss.css">
    <style>

        nav {
            background-color: #1c1c1c; 
            padding: 10px 0;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        nav a {
            color: #e0e0e0; 
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: #ff5722; 
            color: #fff; 
        }

        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding-top: 60px;
        }


        .welcome-container {
            background-color: #1c1c1c;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 400px;
            width: 100%;
            margin: 50px auto;
        }


        h1 {
            color: #ffca28;
            margin-bottom: 20px;
        }


        p {
            font-size: 18px;
            margin-bottom: 20px;
        }


        .btn {
            display: inline-block;
            padding: 10px 20px;
            color: #fff;
            background-color: #ff5722;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #ff784e;
        }
    </style>
</head>
<body>


    <nav>
        <a href="welcome.php">Inicio</a>
        <a href="perfil.php">Perfil</a>
        <a href="estadisticas.php">Estadísticas</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>


    <div class="welcome-container">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
        <p>Has iniciado sesión correctamente.</p>
        <a href="logout.php" class="btn">Cerrar sesión</a>
    </div>

</body>
</html>
