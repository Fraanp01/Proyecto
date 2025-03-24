<?php
// SteamAPI.php

class SteamAPI {
    private $apiKey;
    
    public function __construct() {
        $config = require 'steam_config.php';
        $this->apiKey = $config['api_key'];
    }
    
    /**
     * Obtiene información básica del perfil de un jugador
     * @param string $steamId ID de Steam del jugador
     * @return array Datos del jugador o null en caso de error
     */
    public function getPlayerSummary($steamId) {
        $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$this->apiKey}&steamids={$steamId}";
        
        // Realiza la solicitud
        $response = $this->makeRequest($url);
        
        // Verifica si la respuesta es válida
        if (isset($response['response']['players']) && !empty($response['response']['players'])) {
            return $response['response']['players'][0]; // Devuelve el primer jugador
        } else {
            // Manejo de errores: si no se encuentra el jugador
            return null; // O puedes lanzar una excepción o devolver un mensaje de error
        }
    }
    
    /**
     * Obtiene estadísticas de CS2 para un jugador
     * @param string $steamId ID de Steam del jugador
     * @return array Estadísticas de CS2 o null en caso de error
     */
    public function getCS2Stats($steamId) {
        $url = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key={$this->apiKey}&steamid={$steamId}";
        $response = $this->makeRequest($url);
        
        // Guardar respuesta para depuración
        file_put_contents('debug_cs2_response.json', json_encode($response, JSON_PRETTY_PRINT));
        // Verificación específica para CS2 para asegurarse de que tenemos datos válidos
        if ($response && isset($response['playerstats']) && isset($response['playerstats']['stats']) && !empty($response['playerstats']['stats'])) {
            return $response;
        } else {
            // Registrar el error para ayudar en la depuración
            error_log("No se encontraron estadísticas válidas de CS2 para el usuario $steamId. Respuesta: " . json_encode($response));
            return null;
        }
    }
    
    /**
     * Obtiene los juegos que posee un jugador
     * @param string $steamId ID de Steam del jugador
     * @return array Lista de juegos o null en caso de error
     */
    public function getOwnedGames($steamId) {
        $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$this->apiKey}&steamid={$steamId}&format=json&include_appinfo=1";
        return $this->makeRequest($url);
    }
    
    /**
     * Obtiene estadísticas de CS:GO para un jugador
     * @param string $steamId ID de Steam del jugador
     * @return array Estadísticas de CS:GO o null en caso de error
     */
    public function getCSGOStats($steamId) {
        // El ID de la aplicación de CS:GO es 730
        $url = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key={$this->apiKey}&steamid={$steamId}";
        return $this->makeRequest($url);
    }
    
    /**
     * Realiza una solicitud a la API de Steam
     * @param string $url URL de la API
     * @return array|null Respuesta JSON decodificada o null en caso de error
     */
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Establece un tiempo de espera para la solicitud

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log("Error en la solicitud a la API de Steam: $error");
            return null;
        }
        
        $decodedResponse = json_decode($response, true);
        
        // Verifica si la respuesta es válida
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error al decodificar la respuesta JSON: " . json_last_error_msg());
            return null;
        }
        
        return $decodedResponse;
    }
}