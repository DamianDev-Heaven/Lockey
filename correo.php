<?php
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($email) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos del formulario.']);
    exit;
}

// Ejecutar el script de Python desde PHP
$comando = "python enviar_correo.py $email \"$message\"";
$respuesta = shell_exec($comando);

if (strpos($respuesta, "Correo enviado correctamente") !== false) {
    echo json_encode(['status' => 'success', 'message' => 'Correo enviado correctamente.']);
} else {
    echo json_encode(['status' => 'error', 'message' => $respuesta]);
}
?>
