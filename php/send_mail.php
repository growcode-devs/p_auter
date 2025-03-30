<?php
// Cargar Composer's autoload (necesario si usas Composer)

//Cambiar origins en producción!!!
header("Access-Control-Allow-Origin: *");//!!!
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv=Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos enviados desde el formulario
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $body_content = "
    <h3>Tienes un nuevo mensaje de proyectoaustero.com</h3>
    <p><strong>Nombre:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Mensaje:</strong><br>$message</p>
";
// Crear una nueva instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = 'tls'; 
    $mail->Port = 587; // Puerto para TLS (465 para SSL)

    // Configuración del correo
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($_ENV['SMTP_USERNAME'], 'cats'); // Remitente del correo
    $mail->addAddress('proyectoausterobanda@gmail.com', 'Destinatario'); // Destinatario
    $mail->Subject = 'Nuevo mensaje de contacto';

    // Contenido del correo
    $mail->isHTML(true); // Habilitar HTML
    $mail->Body    = $body_content;
    $mail->AltBody = "Tienes un nuevo mensaje de proyectoaustero.com\nNombre: $name\nEmail: $email\nMensaje: $message\n\nEste es el texto sin formato para clientes que no soportan HTML.";

    // Enviar el correo
    $mail->send();
    http_response_code(200);
    echo json_encode(['message' => '¡Mensaje enviado!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Hubo un problema al enviar el correo: {$mail->ErrorInfo}"]);

}
}
?>
