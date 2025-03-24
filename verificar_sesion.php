<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario está autenticado
 * @param string $redirect_url URL a la que redirigir si no está autenticado
 * @return bool Verdadero si está autenticado, falso si no
 */
function esta_autenticado($redirect_url = null) {
    if (!isset($_SESSION['usuario_id'])) {
        if ($redirect_url) {
            // Guardar la URL actual para redirigir después del login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header("Location: $redirect_url");
            exit;
        }
        return false;
    }
    return true;
}

/**
 * Obtiene el ID del usuario autenticado
 * @return int|null ID del usuario o null si no está autenticado
 */
function obtener_usuario_id() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtiene el nombre del usuario autenticado
 * @return string|null Nombre del usuario o null si no está autenticado
 */
function obtener_usuario_nombre() {
    return $_SESSION['nombre'] ?? null;
}
?>