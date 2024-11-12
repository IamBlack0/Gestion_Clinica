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
                    header('Location: ./login?mensaje=Se ha enviado un email con las instrucciones');
                    exit;
                }
            }
            header('Location: ./restablecerContrasena?error=Error al procesar la solicitud');
            exit;
        }
    }

    private function enviarEmailRestablecimiento($email, $token) {
        $mail = new PHPMailer(true);
    
        try {
            // Configuración del servidor
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Cambiar a DEBUG_SERVER para ver errores
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
                <h1>Restablecer Contraseña - CliniPass</h1>
                <p>Has solicitado restablecer tu contraseña.</p>
                <p>Haz clic en el siguiente enlace para continuar:</p>
                <p><a href="'.$resetLink.'">'.$resetLink.'</a></p>
                <p>Si no solicitaste este cambio, ignora este mensaje.</p>
            ';
    
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