<?php
function conectarDB() {
    $host = "bkwgpnt7d5hd7bpuiwbw-mysql.services.clever-cloud.com";
    $database = "bkwgpnt7d5hd7bpuiwbw";
    $user = "uq6vff78pyt2g5lo";
    $pass = "u6l50PObWFQEFcpTIp5a";

    $sslOptions = [
        PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt', 
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, 
    ];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            PDO::MYSQL_ATTR_SSL_CA => $sslOptions[PDO::MYSQL_ATTR_SSL_CA],
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => $sslOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT],
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}
?>