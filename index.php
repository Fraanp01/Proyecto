<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CStats</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <form method="POST" action="procesarLogin.php">
            <div class="input-group">
                <label for="user">Usuario</label>
                <input type="text" name="user" id="user" required>
            </div>
            <div class="input-group">
                <label for="pass">Contraseña</label>
                <input type="password" name="pass" id="pass" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn login-btn">Iniciar Sesión</button>
                <a href="altalogin.php" class="btn register-btn">Registrarse</a>
            </div>
        </form>
    </div>
</body>
</html>