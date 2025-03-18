<?php
// CSGOStatsScraper.php

/**
 * Clase para extraer estadísticas de mapas de CS:GO de CSGOSTATS.GG
 */
class CSGOStatsScraper {
    private $client;
    
    public function __construct() {
        // Verificar si la extensión cURL está disponible
        if (!function_exists('curl_init')) {
            throw new Exception('La extensión cURL de PHP no está disponible');
        }
    }
    
    /**
     * Obtiene las estadísticas de un jugador por su Steam ID
     * @param string $steamId ID de Steam del jugador
     * @return array Estadísticas del jugador o error
     */
    public function getPlayerStats($steamId) {
        try {
            // Preparamos la URL para obtener datos del jugador
            $url = "https://csgostats.gg/player/{$steamId}";
            
            // Realizamos la solicitud HTTP
            $html = $this->makeRequest($url);
            
            if (!$html) {
                return ['error' => 'No se pudo obtener la página del jugador'];
            }
            
            // Extraemos los datos necesarios usando expresiones regulares
            // Esta es una versión básica, idealmente deberías usar un parser HTML como DOMDocument
            
            // Simulamos datos por ahora (como prueba)
            // En un entorno real, extraerías estos datos de la respuesta HTML
            $stats = [
                'username' => $this->extractUsername($html),
                'maps' => $this->extractMapStats($html)
            ];
            
            // Si no se encontraron datos, devolvemos un error
            if (empty($stats['maps'])) {
                return ['error' => 'No se encontraron estadísticas de mapas'];
            }
            
            return $stats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene estadísticas detalladas de un mapa específico
     * @param string $steamId ID de Steam del jugador
     * @param string $mapName Nombre del mapa
     * @return array Estadísticas detalladas del mapa
     */
    public function getMapDetailedStats($steamId, $mapName) {
        try {
            // En una implementación real, harías scraping de la página específica del mapa
            // Por ahora, simulamos datos para demostración
            
            // Estadísticas genéricas para cualquier mapa
            $detailedStats = [
                'totalMatches' => rand(50, 200),
                'winRate' => rand(40, 70) . '%',
                'avgScore' => rand(15, 30) . '-' . rand(10, 20),
                'bestSide' => (rand(0, 1) == 1) ? 'CT' : 'T',
                'commonPositions' => [
                    'A Site' => rand(20, 40) . '%',
                    'B Site' => rand(20, 40) . '%',
                    'Mid' => rand(15, 30) . '%',
                ],
                'weaponUsage' => [
                    'AK-47' => rand(20, 40) . '%',
                    'M4A4/M4A1-S' => rand(20, 40) . '%',
                    'AWP' => rand(10, 25) . '%',
                    'Desert Eagle' => rand(5, 15) . '%',
                ],
                'recentMatches' => []
            ];
            
            // Crear algunos partidos recientes simulados
            for ($i = 0; $i < 5; $i++) {
                $won = (rand(0, 1) == 1);
                $detailedStats['recentMatches'][] = [
                    'date' => date('Y-m-d', strtotime("-$i days")),
                    'result' => $won ? 'Victoria' : 'Derrota',
                    'score' => ($won ? rand(13, 16) : rand(0, 12)) . '-' . ($won ? rand(0, 12) : rand(13, 16)),
                    'kills' => rand(10, 30),
                    'deaths' => rand(5, 25),
                    'mvps' => rand(0, 5)
                ];
            }
            
            // Personalizaciones específicas según el mapa
            switch (strtolower($mapName)) {
                case 'dust ii':
                    $detailedStats['mapSpecificStats'] = [
                        'Long A Control Rate' => rand(40, 80) . '%',
                        'B Rushes Success Rate' => rand(30, 70) . '%',
                        'Mid to B Success Rate' => rand(35, 75) . '%'
                    ];
                    break;
                case 'mirage':
                    $detailedStats['mapSpecificStats'] = [
                        'A Site Executes Success' => rand(40, 80) . '%',
                        'B Apartments Control' => rand(30, 70) . '%',
                        'Mid Control Rate' => rand(35, 75) . '%'
                    ];
                    break;
                case 'inferno':
                    $detailedStats['mapSpecificStats'] = [
                        'Banana Control Rate' => rand(40, 80) . '%',
                        'A Site Defense Success' => rand(30, 70) . '%',
                        'Apartments Usage' => rand(35, 75) . '%'
                    ];
                    break;
                // Agregar más mapas según sea necesario
            }

            return $detailedStats;
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Realiza una solicitud HTTP a la URL especificada
     * @param string $url URL a solicitar
     * @return string|false Contenido de la respuesta o falso en caso de error
     */
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Error en cURL: $error");
            return false;
        }
        
        return $response;
    }
    
    /**
     * Extrae el nombre de usuario del HTML
     * @param string $html Contenido HTML
     * @return string Nombre de usuario extraído
     */
    private function extractUsername($html) {
        // Aquí deberías implementar la lógica para extraer el nombre de usuario del HTML
        // Por ahora, retornamos un valor simulado
        return 'NombreDeUsuarioSimulado';
    }
    
    /**
     * Extrae las estadísticas de mapas del HTML
     * @param string $html Contenido HTML
     * @return array Estadísticas de mapas
     */
    private function extractMapStats($html) {
        // Aquí deberías implementar la lógica para extraer las estadísticas de mapas del HTML
        // Por ahora, retornamos datos simulados
        return [
            'Dust II' => ['matches' => 100, 'winRate' => '55%'],
            'Mirage' => ['matches' => 80, 'winRate' => '60%'],
            'Inferno' => ['matches' => 90, 'winRate' => '50%'],
        ];
    }
}