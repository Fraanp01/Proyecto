<?php

function conectarDB() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=tu_base_de_datos", "usuario", "contraseña");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        return null;
    }
}


function guardarDatos($user, $pass) {
    $pdo = conectarDB();

    if ($pdo != null) {
        
        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

        
        $consulta = "INSERT INTO login (user, pass) VALUES (:paramUser, :paramPass)";
        $resul = $pdo->prepare($consulta);

        if ($resul != null) {
            
            if ($resul->execute(["paramUser" => $user, "paramPass" => $hashedPass])) {
                
                session_start();
                $_SESSION["username"] = $user; 

                
                header("Location: welcome.php");
                exit(); 
            } else {
                echo "ERROR: Registro NO insertado <br>";
            }
        }
    }
}
?>
