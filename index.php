<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
    <style>
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        
        .toggle-password:hover {
            color: #4361ee;
        }
        
        /* Ajustar el padding del campo de contraseña para evitar que el texto se solape con el icono */
        #password {
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <svg class="logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill="#4361ee" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
        </svg>
        
        <h2>Iniciar Sesión</h2>
        
        <form action="Logica_login.php" method="POST" class="form">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Ingresa tu usuario" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                    <i class="toggle-password fas fa-eye" title="Mostrar contraseña"></i>
                </div>
                
                <a href="recuperar_cuenta.php" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Crea una</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.querySelector('#password');
            
            togglePassword.addEventListener('click', function() {
                // Cambiar el tipo de input entre password y text
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Cambiar el icono entre ojo y ojo tachado
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
                
                // Cambiar el título según el estado
                this.setAttribute('title', 
                    type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña'
                );
            });
        });
    </script>
</body>
</html>