<?php
function cerrarsesion(){
    // logout.php
    session_start();
    session_unset(); // Eliminar todas las variables de sesión
    session_destroy(); // Destruir la sesión
    header('Location: index.php'); // Redirigir al login
    exit();
}
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_sesion'])){
        cerrarsesion();
    }
?>
