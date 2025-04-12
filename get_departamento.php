<?php
// filepath: c:\xampp\htdocs\Prototipo\get_departamento.php
session_start();
include('config/conexion.php');

// Verificar autenticación
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $departamento_id = $_GET['id'];
    
    try {
        // Obtener datos del departamento (solo id y nombre)
        $stmt = $pdo->prepare("SELECT id, nombre FROM departamentos WHERE id = ?");
        $stmt->execute([$departamento_id]);
        $departamento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($departamento) {
            // Devolver los datos como JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'id' => $departamento['id'],
                'nombre' => $departamento['nombre']
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Departamento no encontrado']);
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit();
}

// Si no es GET o no se proporcionó ID, devolver error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
?>