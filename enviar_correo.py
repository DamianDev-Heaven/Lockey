# enviar_correo.py
import smtplib
import sys
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

# Obtener datos desde la línea de comandos
email_destino = sys.argv[1]
mensaje = sys.argv[2]

# Configuración del correo
email_origen = "davidesau140@gmail.com"
password = "trpt rjjr louo opye"
asunto = "Recuperación de contraseña"

# Construir el correo
mensaje_correo = MIMEMultipart()
mensaje_correo["From"] = email_destino
mensaje_correo["To"] =  email_origen
mensaje_correo["Subject"] = asunto

# Cuerpo del correo
mensaje_correo.attach(MIMEText(mensaje, "plain"))

try:
    # Conectar al servidor SMTP de Gmail
    servidor = smtplib.SMTP("smtp.gmail.com", 587)
    servidor.starttls()
    servidor.login(email_origen, password)
    servidor.sendmail(email_destino, email_origen, mensaje_correo.as_string())
    servidor.quit()
    print("Correo enviado correctamente")
except Exception as e:
    print(f"Error al enviar el correo: {e}")
