<?php
session_start();

// Verificaciones de sesión
if (!isset($_SESSION['usuario_id']) && isset($_SESSION['idUsuario'])) {
    $_SESSION['usuario_id'] = $_SESSION['idUsuario'];
}

// Si no hay sesión, redirige
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['idUsuario']) && !isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Nombre de usuario para mostrar
$nombre_usuario = $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamientos | CStats</title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 80px auto;
            padding: 30px;
            background: rgba(40, 40, 40, 0.9);
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #ffca28;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
            flex-wrap: wrap;
        }
        .tab {
            padding: 12px 24px;
            margin: 0 10px 10px 10px;
            cursor: pointer;
            border-radius: 30px;
            background-color: #333;
            color: #fff;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        .tab:hover {
            background-color: #444;
        }
        .tab.active {
            background-color: #ffca28;
            color: #333;
        }
        .content-section {
            display: none;
            padding: 20px;
            animation: fadeIn 0.5s ease;
        }
        .content-section.active {
            display: block;
        }
        .aim-training, .maps-utility, .team-strategy {
            background-color: rgba(50, 50, 50, 0.5);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .exercise, .strategy-item {
            background-color: rgba(60, 60, 60, 0.7);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ffca28;
        }
        .map-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220 px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .map-item, .strategy-item {
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .map-item:hover, .strategy-item:hover {
            background-color: rgba(255, 202, 40, 0.2);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
    <script>
        function showSection(section) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(sec => {
                sec.classList.remove('active');
            });
            document.getElementById(section).classList.add('active');

            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.tab[data-section="${section}"]`).classList.add('active');
        }

        function showResources(mapa) {
            const recursosDiv = document.getElementById('recursos');
            recursosDiv.innerHTML = ''; // Limpiar contenido previo
            const recursos = {
                'inferno': [
                    { nombre: 'Smokes de Inferno', descripcion: 'Los smokes más usados en Inferno para controlar el mapa.', video: 'https://www.youtube.com/watch?v=MnWZuVCA-Cc' },
                    { nombre: 'Flashbangs de Inferno', descripcion: 'Cómo usar flashbangs efectivamente en Inferno.', video: 'https://www.youtube.com/watch?v=5ihzFtJdVoI' }
                ],
                'dust2': [
                    { nombre: 'Smokes de Dust II', descripcion: 'Los smokes más efectivos para Dust II.', video: 'https://www.youtube.com/watch?v=9L9D5yc8LuE' },
                    { nombre: 'Molotovs de Dust II', descripcion: 'Uso de molotovs en Dust II para controlar áreas.', video: 'https://www.youtube.com/watch?v=wYrB_XgRnww' }
                ],
                'mirage': [
                    { nombre: 'Smokes de Mirage', descripcion: 'Los smokes más efectivos para Mirage.', video: 'https://www.youtube.com/watch?v=NCsFNAMdGn0' },
                    { nombre: 'Flashbangs de Mirage', descripcion: 'Cómo usar flashbangs efectivamente en Mirage.', video: 'https://www.youtube.com/watch?v=5ihzFtJdVoI' }
                ],
                'nuke': [
                    { nombre: 'Smokes de Nuke', descripcion: 'Los smokes más importantes para Nuke.', video: 'https://www.youtube.com/watch?v=example1' },
                    { nombre: 'Molotovs de Nuke', descripcion: 'Uso de molotovs en Nuke para controlar áreas clave.', video: 'https://www.youtube.com/watch?v=example2' }
                ],
                'overpass': [
                    { nombre: 'Smokes de Overpass', descripcion: 'Los smokes más efectivos para Overpass.', video: 'https://www.youtube.com/watch?v=example3' },
                    { nombre: 'Flashbangs de Overpass', descripcion: 'Cómo usar flashbangs en Overpass.', video: 'https://www.youtube.com/watch?v=example4' }
                ]
            };

            recursos[mapa].forEach(recurso => {
                recursosDiv.innerHTML += `
                    <div class="exercise">
                        <h5>${recurso.nombre}</h5>
                        <p><strong>Descripción:</strong> ${recurso.descripcion}</p>
                        <a href="${recurso.video}" target="_blank">Ver Video</a>
                    </div>
                `;
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Entrenamientos</h1>
        <div class="tabs">
            <div class="tab active" data-section="aim" onclick="showSection('aim')">Mejora tu Puntería</div>
            <div class="tab" data-section="utility" onclick="showSection('utility')">Utilidad por Mapas</div>
            <div class="tab" data-section="team" onclick="showSection('team')">Estrategias de Equipo</div>
        </div>

        <div id="aim" class="content-section active">
            <h2>Rutinas de Puntería</h2>
            <div class="aim-training">
                <h3>Rutina Básica</h3>
                <p>Mejora tu puntería con ejercicios específicos.</p>
                <div class="exercise">
                    <h4>Gridshot Ultimate</h4>
                    <p>Duración: 10 minutos</p>
                    <p>Mejora tu velocidad y precisión disparando a objetivos distribuidos en una cuadrícula.</p>
                    <a href="https://www.youtube.com/watch?v=TsHKZGHCnpk" target="_blank">Ver Video</a>
                </div>
                <div class="exercise">
                    <h4>Spidershot Precision</h4>
                    <p>Duración: 5 minutos</p>
                    <p>Entrena tu precisión con objetivos que aparecen a diferentes distancias.</p>
                    <a href="https://www.youtube.com/watch?v=ygVxdZj91Rs" target="_blank">Ver Video</a>
                </div>
            </div>
        </div>

        <div id="utility" class="content-section">
            <h2>Utilidad por Mapas</h2>
            <div class="maps-utility">
                <h3>Selecciona un Mapa</h3>
                <div class="map-grid">
                    <div class="map-item" onclick="showResources('inferno')">
                        <h4>Inferno</h4>
                    </div>
                    <div class="map-item" onclick="showResources('dust2')">
                        <h4>Dust II</h4>
                    </div>
                    <div class="map-item" onclick="showResources('mirage')">
                        <h4>Mirage</h4>
                    </div>
                    <div class="map-item" onclick="showResources('nuke')">
                        <h4>Nuke</h4>
                    </div>
                    <div class="map-item" onclick="showResources('overpass')">
                        <h4>Overpass</h4>
                    </div>
                </div>
            </div>
            <div id="recursos" class="resources"></div>
        </div>

        <div id="team" class="content-section">
            <h2>Estrategias de Equipo</h2>
            <div class="team-strategy">
                <h3>Mejora tu Juego en Equipo</h3>
                <div class="strategy-item">
                    <h4>Comunicación Efectiva</h4>
                    <p>Aprende a comunicarte con tu equipo para coordinar estrategias y movimientos.</p>
                    <a href="https://www.youtube.com/watch?v=example5" target="_blank">Ver Video</a>
                </div>
                <div class="strategy-item">
                    <h4>Formaciones de Equipo</h4>
                    <p>Conoce las mejores formaciones para maximizar el rendimiento de tu equipo.</p>
                    <a href="https://www.youtube.com/watch?v=example6" target="_blank">Ver Video</a>
                </div>
                <div class="strategy-item">
                    <h4>Roles en el Equipo</h4>
                    <p>Entiende la importancia de los roles dentro del equipo y cómo desempeñarlos.</p>
                    <a href="https://www.youtube.com/watch?v=example7" target="_blank">Ver Video</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>