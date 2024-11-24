<?php
require_once './Config/DataBase.php';
require_once 'Public/PHPMailer-6.9.2/src/Exception.php';
require_once 'Public/PHPMailer-6.9.2/src/PHPMailer.php';
require_once 'Public/PHPMailer-6.9.2/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class PasswordController {
    private $userModel;
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->userModel = new User($this->db);
    }

    public function solicitarRestablecimiento() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'];
            $this->userModel->email = $email;
            
            if($this->userModel->emailExiste($email)) {
                $token = $this->userModel->generarTokenRestablecimiento();
                
                if($token && $this->enviarEmailRestablecimiento($email, $token)) {
                    // En lugar de usar header, retornamos a la página con un mensaje
                    echo "<script>
                        window.location.href = './restablecerContrasena?mensaje=Se ha enviado un enlace a tu correo electrónico';
                    </script>";
                    exit;
                }
            }
            // En caso de error
            echo "<script>
                window.location.href = './restablecerContrasena?error=No se pudo procesar la solicitud. Por favor, intenta de nuevo.';
            </script>";
            exit;
        }
    }

    private function enviarEmailRestablecimiento($email, $token) {
        $mail = new PHPMailer(true);
    
        try {
            // Configuración del servidor
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'clinipasscontacto@gmail.com'; 
            $mail->Password = 'iusi qhud gweb znug'; // Asegúrate que esta es una contraseña de aplicación
            $mail->SMTPSecure = 'tls'; // Cambiar a TLS
            $mail->Port = 587; // Puerto para TLS
    
            // Configuración del correo
            $mail->setFrom('clinipasscontacto@gmail.com', 'CliniPass');
            $mail->addAddress($email);
            $mail->CharSet = 'UTF-8';
    
            // Contenido
            $resetLink = "http://localhost/PROGRAMACION/Gestion_Clinica/restablecer?token=" . $token; // Ajusta la URL según tu estructura

            $mensajeHTML = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        h1 {
            color: #566a7f;
            font-size: 24px;
            margin: 0 0 20px;
        }
        .content {
            padding: 30px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #696cff;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            color: #666666;
            font-size: 14px;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://i.ibb.co/nDsJh28/Captura-de-pantalla-2024-11-24-140125.png" alt="Medisfera Logo" class="logo">
            <h1>Restablecer tu contraseña</h1>
        </div>
        <div class="content">
            <p>Has solicitado restablecer tu contraseña en Medisfera.</p>
            <p>Haz clic en el siguiente botón para continuar con el proceso:</p>
            <a href="' . $resetLink . '" class="btn">Restablecer Contraseña</a>
            <p style="margin-top: 30px; color: #666;">Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.</p>
        </div>
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>&copy; ' . date('Y') . ' Medisfera. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>';
    
            $mail->isHTML(true);
            $mail->Subject = 'Restablecer Contraseña - CliniPass';
            $mail->Body = $mensajeHTML;
            $mail->AltBody = 'Accede a este enlace para restablecer tu contraseña: ' . $resetLink;
    
            $result = $mail->send();
            error_log("Email enviado correctamente"); // Agregar log
            return $result;
    
        } catch (Exception $e) {
            error_log("Error al enviar email: " . $mail->ErrorInfo);
            return false;
        }
    }

    public function actualizarContrasena($password, $token) {
        try {
            // Ya tenemos this->userModel inicializado en el constructor
            return $this->userModel->actualizarPassword($password, $token);
        } catch (Exception $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }
}