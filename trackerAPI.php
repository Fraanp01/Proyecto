<?php
class TrackerAPI {
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
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