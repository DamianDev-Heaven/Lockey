<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Contacto</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-color: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .contact-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            transition: transform 0.3s ease;
        }
        
        .contact-container:hover {
            transform: translateY(-5px);
        }
        
        h1 {
            color: var(--dark-color);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 500;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: var(--secondary-color);
        }
        
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-top: 20px;
            display: none;
        }
        
        @media (max-width: 600px) {
            .contact-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h1>Recupera tu cuenta</h1>
        
        <form id="contactForm" action="correo.php" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="tucorreo@ejemplo.com">
            </div>
            
            <div class="form-group">
                <label for="message">Escriba su departamento y nombre</label>
                <textarea id="message" name="message" required placeholder="Escribe tu mensaje aquí..."></textarea>
            </div>
            
            <button type="submit">Enviar Mensaje</button>
    
        </form>
        <br/>
        <a href="index.php" class="btn btn-primary">Regresar al Login</a>
        <div id="successMessage" class="success-message" style="display: none;">
            ¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.
        </div>
        <div id="errorMessage" class="error-message" style="display: none;">
            Error al enviar el mensaje. Por favor, inténtalo de nuevo más tarde.
        </div>
    </div>
    
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault(); 
        
            // Ocultar mensajes previos
            document.getElementById('successMessage').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
        
            // Enviar el formulario mediante AJAX usando fetch()
            fetch('correo.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.text()) // Obtiene la respuesta como texto plano
            .then(text => {
                console.log('Respuesta del servidor:', text); // Imprime la respuesta en la consola
                try {
                    const data = JSON.parse(text); // Intenta convertir la respuesta a JSON
                    if (data.status === 'success') {
                        document.getElementById('successMessage').style.display = 'block';
                    } else {
                        document.getElementById('errorMessage').textContent = data.message;
                        document.getElementById('errorMessage').style.display = 'block';
                    }
                } catch (error) {
                    console.error('Error al procesar la respuesta:', error);
                    document.getElementById('errorMessage').textContent = 'Ocurrió un error inesperado.';
                    document.getElementById('errorMessage').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorMessage').textContent = 'Ocurrió un error inesperado.';
                document.getElementById('errorMessage').style.display = 'block';
            });
            
            
        });
    </script>
</body>
</html>