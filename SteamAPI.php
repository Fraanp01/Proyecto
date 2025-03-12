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
        return $this->makeRequest($url);
    }
    
    /**
     * Obtiene estadísticas de CS2 para un jugador
     * @param string $steamId ID de Steam del jugador
     * @return array Estadísticas de CS2 o null en caso de error
     */
    public function getCS2Stats($steamId) {
        // El ID de la aplicación de CS2 es 730 (mismo que CS:GO)
        $url = "https://api.steampowered.com/ISteamUserStats/GetUserStatsForGame/v0002/?appid=730&key={$this->apiKey}&steamid={$steamId}";
        return $this->makeRequest($url);
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
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return null;
        }
        
        return json_decode($response, true);
    }
}