<?php
class Usuario {
    public static function iniciarSesion($user, $pass) {
        
    }

    public static function verificarSesion() {
        if (!isset($_SESSION["idUsuario"])) {
            header("Location: index.php");
            exit();
        }
    }

    public static function cerrarSesion() {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
?>
