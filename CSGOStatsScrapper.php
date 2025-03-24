<?php
// CSGOStatsScraper.php - Versión con iframe

class CSGOStatsScrapper {
    
    /**
     * Obtiene las estadísticas de un jugador por su Steam ID
     * @param string $steamId ID de Steam del jugador
     * @return array Información para mostrar iframe
     */
    public function getPlayerStats($steamId) {
        // Validar que sea un Steam ID válido
        if (!preg_match('/^[0-9]{17}$/', $steamId)) {
            return ['error' => 'Por favor, introduce un Steam ID válido (17 dígitos)'];
        }
        
        // Crear URL de csstats.gg
        $url = "https://csstats.gg/player/{$steamId}";
        
        // Intentar hacer una solicitud mínima para verificar si el jugador existe
        $headers = get_headers($url);
        
        // Verificar si la página existe
        if (strpos($headers[0], '404') !== false) {
            return ['error' => 'El jugador no existe en csstats.gg'];
        }
        
        // Extraer el nombre del usuario de una manera simple
        $name = "Jugador " . substr($steamId, -5);
        
        // Devolver información para iframe
        return [
            'username' => $name,
            'steamId' => $steamId,
            'iframeUrl' => $url,
            'directUrl' => $url
        ];
    }
}