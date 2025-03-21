
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
        
        /* Estilos del header y navegación */
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
        }
        
        .logo {
            margin-right: 30px;
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
            gap: 15px;
        }
        
        .navigation a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            transition: all var(--transition-speed) ease;
            border-radius: 5px;
            display: flex; align-items: center;
        }
        
        .navigation a i {
            margin-right: 5px;
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
            margin-left: auto;
            padding: 8px 15px;
            background-color: #ff5722;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-logout i {
            margin-right: 5px;
        }
        
        .btn-logout:hover {
            background-color: #ff784e;
        }
        
        .hero-section {
            text-align: center;
            padding: 60px 20px;
            margin-top: 80px; /* Espacio para el header fijo */
        }
        
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
        }
        
        .hero-section p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px; 
        }
        
        .features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin-bottom: 60px;
}

.feature-card {
    background-color: rgba(40, 40, 40, 0.85);
    border-radius: 15px;
    padding: 30px;
    box-shadow: var(--box-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
}
        
        .feature-icon {
            font-size: 3rem; 
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .feature-card h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .feature-card p {
            color: var(--text-light);
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            background-color: var(--accent-color);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color var(--transition-speed) ease;
        }
        
        .btn:hover {
            background-color: var(--accent-hover);
        }
        
        footer {
            background-color: var(--background-medium);
            color: var(--text-light);
            text-align: center;
            padding: 15px;
            margin-top: 20px;
        }
        /* Estilos solo para la sección de Últimas Noticias */
        .additional-section {
            background-color: rgba(40, 40, 40, 0.85);
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0 40px 0;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .additional-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }
        
        .additional-section h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.8rem;
        }
        
        .additional-section p {
            color: var(--text-light);
            margin-bottom: 20px;
            text-align: justify;
            line-height: 1.6;
        }
        
        .additional-section ul {
            list-style-type: none;
            padding: 0;
        }
        
        .additional-section li {
            background-color: rgba(255, 202, 40, 0.1);
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
            transition: background-color var(--transition-speed) ease;
            border-left: 3px solid var(--primary-color);
        }
        
        .additional-section li:hover {
            background-color: rgba(255, 202, 40, 0.2);
        }
        
        .additional-section strong {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    
    <header>
        <div class="header-container">
            <div class="nav-left">
                <div class="logo">
                    <img src="img/logo.png" alt="Logo"> 
                </div>
                <div class="navigation">
                    <a href="principal.php" class="active"><i class="fas fa-home"></i> Inicio</a>
                    <a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas</a>
                    <a href="partidas.php"><i class="fas fa-gamepad"></i> Partidas</a>
                    <a href="perfil.php"><i class="fas fa-user"></i> Perfil</a>
                    <a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a>
                </div>
            </div>
            <div class="nav-right">
                <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
            </div>
        </div>
    </header>

    <div class="content">
        <div class="hero-section">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
            <p>Explora tus estadísticas, revisa tus partidas y mejora tu juego.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h2>Estadísticas</h2>
                <p>Aquí puedes ver un resumen de tus estadísticas recientes.</p>
                <a href="estadisticas.php" class="btn">Ver más</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-gamepad"></i></div>
                <h2>Partidas</h2>
                <p>Revisa tus partidas recientes y analiza tu rendimiento.</p>
                <a href="partidas.php" class="btn">Ver más</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-lightbulb"></i></div>
                <h2>Estrategias</h2>
                <p>Explora estrategias recomendadas para mejorar tu juego.</p>
                <a href="estrategias.php" class="btn">Ver más</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-comments"></i></div>
                <h2>Feedback</h2>
                <p>Envía tus comentarios y sugerencias para mejorar la aplicación.</p>
                <a href="feedback.php" class="btn">Enviar Feedback</a>
            </div>
        </div>

        <div class="additional-section">
            <h2>Últimas Noticias</h2>
            <p>Mantente al tanto de las últimas actualizaciones y eventos en C Stats. Aquí encontrarás información sobre nuevas características, torneos y más.</p>
            <ul>
                <li><strong>Actualización de estadísticas:</strong> Nuevas métricas disponibles para un análisis más profundo.</li>
                <li><strong>Torneo mensual:</strong> Participa en nuestro torneo y gana premios increíbles.</li>
                <li><strong>Mejoras en la interfaz:</strong> Hemos optimizado la navegación para una mejor experiencia de usuario.</li>
            </ul>
        </div>
    </div>

    <footer>
        <p>&copy; 2023 CStats. Todos los derechos reservados.</p>
    </footer>
</body>
</html>