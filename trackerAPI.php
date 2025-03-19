<?php
class TrackerAPI {
    private $apiKey;

    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey;
    }

    public function getPlayerStats($playerId, $platform = 'steam') {
        $url = "https://public-api.tracker.gg/v2/csgo/standard/profile/{$platform}/{$playerId}";
        $headers = [
            "TRN-Api-Key: {$this->apiKey}",
            "Accept: application/json",
            "Accept-Encoding: gzip"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }
        
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        // Procesar los datos para tener un formato más fácil de usar
        $stats = [];
        
        if (isset($data['data']['segments'][0]['stats'])) {
            $rawStats = $data['data']['segments'][0]['stats'];
            
            // Extraer estadísticas comunes
            $stats = [
                'kills' => $rawStats['kills']['value'] ?? 0,
                'deaths' => $rawStats['deaths']['value'] ?? 0,
                'kd_ratio' => $rawStats['kd']['value'] ?? 0,
                'headshots' => $rawStats['headshots']['value'] ?? 0,
                'headshot_percentage' => $rawStats['headshotPct']['value'] ?? 0,
                'matches_played' => $rawStats['matchesPlayed']['value'] ?? 0,
                'wins' => $rawStats['wins']['value'] ?? 0,
                'win_rate' => $rawStats['wlPercentage']['value'] ?? 0,
                // Puedes agregar más estadísticas según lo que proporcione la API
            ];
        } else {
            // Si no hay datos disponibles, devolver valores predeterminados
            $stats = [
                'kills' => 0,
                'deaths' => 0,
                'kd_ratio' => 0,
                'headshots' => 0,
                'headshot_percentage' => 0,
                'matches_played' => 0,
                'wins' => 0,
                'win_rate' => 0
            ];
        }
        
        return $stats;
    }

    public function getMapStats($platform, $playerId) {
        $url = "https://public-api.tracker.gg/v2/csgo/standard/profile/{$platform}/{$playerId}/segments/map";
        $headers = [
            "TRN-Api-Key: {$this->apiKey}",
            "Accept: application/json",
            "Accept-Encoding: gzip"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }
}
?>