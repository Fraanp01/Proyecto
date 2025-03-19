<?php
// Iniciamos la sesión antes de cualquier salida
session_start();

// Verificar qué variables de sesión están disponibles
if (!isset($_SESSION['usuario_id']) && isset($_SESSION['idUsuario'])) {
    $_SESSION['usuario_id'] = $_SESSION['idUsuario'];
}

// Si no hay sesión, redirigimos al login principal
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['idUsuario']) && !isset($_SESSION['username'])) {
    header('Location: index.php'); // Tu página de login principal
    exit;
}

// Usamos la variable de sesión que esté disponible para el nombre de usuario
$nombre_usuario = $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario';
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['idUsuario'] ?? null;

// Definir mapas y sus recursos
$mapas = [
    'inferno' => [
        'titulo' => 'Inferno',
        'recursos' => [
            [
                'nombre' => 'Smokes de Inferno',
                'descripcion' => 'Los smokes más usados en Inferno para controlar el mapa.',
                'video' => 'https://www.youtube.com/watch?v=smokes_inferno'
            ],
            [
                'nombre' => 'Flashbangs de Inferno',
                'descripcion' => 'Cómo usar flashbangs efectivamente en Inferno.',
                'video' => 'https://www.youtube.com/watch?v=flashbangs_inferno'
            ]
        ]
    ],
    'dust2' => [
        'titulo' => 'Dust II',
        'recursos' => [
            [
                'nombre' => 'Smokes de Dust II',
                'descripcion' => 'Los smokes más efectivos para Dust II.',
                'video' => 'https://www.youtube.com/watch?v=smokes_dust2'
            ],
            [
                'nombre' => 'Molotovs de Dust II',
                'descripcion' => 'Uso de molotovs en Dust II para controlar áreas.',
                'video' => 'https://www.youtube.com/watch?v=molotovs_dust2'
            ]
        ]
    ]
];

// Definimos los tipos de entrenamientos disponibles
$tipos_entrenamiento = [
    [
        'id' => 'aim',
        'titulo' => 'Mejora tu Puntería (Aim)',
        'descripcion' => 'Entrenamientos específicos para mejorar tu precisión, velocidad de reacción y control de disparo.',
        'icono' => 'fa-crosshairs',
        'color' => '#ff5722',
        'rutinas' => [
            [
                'titulo' => 'Rutina de Aim Básica (AimLab)',
                'descripcion' => 'Rutina completa para principiantes que quieren mejorar su puntería base.',
                'duracion_total' => '30 minutos',
                'dificultad' => 'Principiante',
                'ejercicios' => [
                    [
                        'nombre' => 'Gridshot Ultimate',
                        'duracion' => '10 minutos',
                        'descripcion' => 'Mejora tu velocidad y precisión disparando a objetivos distribuidos en una cuadrícula.',
                        'consejos' => 'Intenta mantener un ritmo constante y no sacrifiques precisión por velocidad.',
                        'video' => 'https://www.youtube.com/watch?v=TsHKZGHCnpk'
                    ],
                    [
                        'nombre' => 'Spidershot Precision',
                        'duracion' => '5 minutos',
                        'descripcion' => ' Entrena tu precisión con objetivos que aparecen a diferentes distancias.',
                        'consejos' => 'Tómate tu tiempo para hacer cada disparo con precisión.',
                        'video' => 'https://www.youtube.com/watch?v=ygVxdZj91Rs'
                    ],
                    [
                        'nombre' => 'Microshot Speed',
                        'duracion' => '5 minutos',
                        'descripcion' => 'Ejercicio para mejorar micromovimientos precisos.',
                        'consejos' => 'Concéntrate en la precisión en objetivos pequeños y cercanos.',
                        'video' => 'https://www.youtube.com/watch?v=JZDkAdSUKy4'
                    ],
                    [
                        'nombre' => 'Strafetrack',
                        'duracion' => '10 minutos',
                        'descripcion' => 'Mejora tu tracking mientras te mueves (strafing).',
                        'consejos' => 'Mantén tu mira en el objetivo mientras te mueves de lado a lado.',
                        'video' => 'https://www.youtube.com/watch?v=gPa0l3mCFzk'
                    ]
                ]
            ],
            [
                'titulo' => 'Rutina de Aim Avanzada (KovaaK)',
                'descripcion' => 'Ejercicios diseñados para jugadores con experiencia que buscan perfeccionar su puntería.',
                'duracion_total' => '45 minutos',
                'dificultad' => 'Avanzado',
                'ejercicios' => [
                    [
                        'nombre' => 'Flicking Challenge',
                        'duracion' => '10 minutos',
                        'descripcion' => 'Entrena tu capacidad de reacción y precisión en disparos rápidos.',
                        'consejos' => 'Practica el flicking entre objetivos lejanos y cercanos.',
                        'video' => 'https://www.youtube.com/watch?v=example5'
                    ],
                    [
                        'nombre' => 'Tracking Challenge',
                        'duracion' => '15 minutos',
                        'descripcion' => 'Mejora tu habilidad para seguir objetivos en movimiento.',
                        'consejos' => 'Mantén la calma y ajusta tu sensibilidad si es necesario.',
                        'video' => 'https://www.youtube.com/watch?v=example6'
                    ],
                    [
                        'nombre' => 'Target Switching',
                        'duracion' => '10 minutos',
                        'descripcion' => 'Ejercicio para cambiar rápidamente entre diferentes objetivos.',
                        'consejos' => 'Asegúrate de mantener la precisión al cambiar de objetivo.',
                        'video' => 'https://www.youtube.com/watch?v=example7'
                    ],
                    [
                        'nombre' => 'Reflex Training',
                        'duracion' => '10 minutos',
                        'descripcion' => 'Entrenamiento para mejorar tus reflejos en situaciones de combate.',
                        'consejos' => 'Practica con diferentes configuraciones de velocidad.',
                        'video' => 'https://www.youtube.com/watch?v=example8'
                    ]
                ]
            ]
        ]
    ],
    [
        'id' => 'utilidad',
        'titulo' => 'Utilidad por Mapas',
        'descripcion' => 'Aprende a usar utilidades específicas en diferentes mapas.',
        'icono' => 'fa-tools',
        'color' => '#4caf50',
        'mapas' => $mapas
    ],
];

// Verificamos si se ha seleccionado un tipo de entrenamiento específico
$tipo_seleccionado = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$mapa_seleccionado = isset($_GET['mapa']) ? $_GET['mapa'] : null;

// Si se seleccionó un tipo, cargar los entrenamientos específicos
$entrenamientos_especificos = [];

if ($tipo_seleccionado) {
    foreach ($tipos_entrenamiento as $tipo) {
        if ($tipo['id'] === $tipo_seleccionado) {
            if ($tipo['id'] === 'utilidad' && $mapa_seleccionado) {
                if (isset($tipo['mapas'][$mapa_seleccionado])) {
                    $entrenamientos_especificos = $tipo['mapas'][$mapa_seleccionado]['recursos'];
                }
            } else {
                $entrenamientos_especificos = $tipo['rutinas'];
            }
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrenamientos | CStats </title>
    <link rel="stylesheet" href="css.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
            background: rgba(40, 40, 40, 0.85);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #ffca28;
        }
        .tipo-entrenamiento {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .tipo-entrenamiento:hover {
            background-color: rgba(255, 202, 40, 0.2);
        }
        .mapa {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.05);
            display: none; /* Ocultar por defecto */
            max-height: 300px; /* Altura máxima para scroll */
            overflow-y: auto; /* Scroll vertical */
        }
        .mostrar {
            display: block; /* Mostrar cuando se activa */
        }
        .recursos a {
            color: #ffca28;
            text-decoration: none;
        }
        .recursos a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function toggleRutina(id) {
            const rutina = document.getElementById(id);
            rutina.classList.toggle('mostrar');
        }
        function mostrarRecursos(mapa) {
            const recursosDiv = document.getElementById('recursos');
            if (recursosDiv.classList.contains('mostrar') && recursosDiv.dataset.mapa === mapa) {
                recursosDiv.classList.remove('mostrar'); // Ocultar si ya está visible
                return;
            }
            recursosDiv.innerHTML = ''; // Limpiar contenido previo
            const recursos = <?php echo json_encode($mapas); ?>[mapa].recursos;
            recursos.forEach(recurso => {
                recursosDiv.innerHTML += `
                    <div class="recurso">
                        <h6>${recurso.nombre}</h6>
                        <p><strong>Descripción:</strong> ${recurso.descripcion}</p>
                        <a href="${recurso.video}" target="_blank">Ver Video</a>
                    </div>
                `;
            });
            recursosDiv.classList.add('mostrar'); // Mostrar recursos
            recursosDiv.dataset.mapa = mapa; // Guardar el mapa actual
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Tipos de Entrenamientos</h1>
        <?php foreach ($tipos_entrenamiento as $tipo): ?>
            <div class="tipo-entrenamiento" onclick="toggleRutina('<?php echo $tipo['id']; ?>-rutinas')">
                <h2><?php echo htmlspecialchars($tipo['titulo']); ?></h2>
                <p><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                <i class="fas <?php echo $tipo['icono']; ?>" style="color: <?php echo $tipo['color']; ?>"></i>
            </div>
            <div id="<?php echo $tipo['id']; ?>-rutinas" class="rutina" style="display: none;">
                <h3>Rutinas para <?php echo htmlspecialchars($tipo['titulo']); ?></h3>
                <?php if (isset($tipo['rutinas'])): ?>
                    <?php foreach ($tipo['rutinas'] as $rutina): ?>
                        <div class="entrenamiento">
                            <h4><?php echo htmlspecialchars($rutina['titulo']); ?></h4>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($rutina['descripcion']); ?></p>
                            <p><strong>Duración Total:</strong> <?php echo htmlspecialchars($rutina['duracion_total']); ?></p>
                            <p><strong>Dificultad:</strong> <?php echo htmlspecialchars($rutina['dificultad']); ?></p>
                            <h5>Ejercicios:</h5>
                            <ul>
                                <?php foreach ($rutina['ejercicios'] as $ejercicio): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($ejercicio['nombre']); ?></strong> - <?php echo htmlspecialchars($ejercicio['duracion']); ?>
                                        <p><?php echo htmlspecialchars($ejercicio['descripcion']); ?></p>
                                        <p><em>Consejos:</em> <?php echo htmlspecialchars($ejercicio['consejos']); ?></p>
                                        <a href="<?php echo htmlspecialchars($ejercicio['video']); ?>" target="_blank">Ver Video</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php elseif (isset($tipo['mapas'])): ?>
                    <h3>Selecciona un Mapa para Utilidad</h3>
                    <?php foreach ($tipo['mapas'] as $mapa_id => $mapa): ?>
                        <div class="tipo-entrenamiento" onclick="mostrarRecursos('<?php echo htmlspecialchars($mapa_id); ?>')">
                            <h4><?php echo htmlspecialchars($mapa['titulo']); ?></h4>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div id="recursos" class="mapa"></div>
    </div>
</body>
</html>