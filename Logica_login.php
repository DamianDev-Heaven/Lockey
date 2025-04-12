<?php
session_start();
include('config\conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hashed_contrasenha = password_hash($password, PASSWORD_BCRYPT);
    try {
        // Consultar el hash de la contraseña
        $sql = "SELECT u.id, u.username, u.password_hash, u.empleado_id, r.nombre AS rol_nombre 
                FROM usuarios u 
                JOIN roles r ON u.rol_id = r.id 
                WHERE u.username = :username";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hash_almacenado = $user['password_hash'];

            // Comparar usando password_verify
            if (password_verify($password, $hash_almacenado)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['rol'] = $user['rol_nombre'];
                $_SESSION['empleado_id'] = $user['empleado_id'];

                // Redirigir según el rol
                if ($user['rol_nombre'] == 'administrador') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: user_dashboard.php');
                }
                exit();
            } else {
                echo "<script>
                        alert('Contraseña incorrecta');
                        window.history.back();
                      </script>";
                exit();
            }
        } else {
            echo "<script>
                    alert('Usuario No Encontrado echo($hashed_contrasenha);');
                    window.history.back();
                  </script>";

            exit();
        }
    } catch (PDOException $e) {
        echo "Error en la conexión: " . $e->getMessage();
    }

}
?>