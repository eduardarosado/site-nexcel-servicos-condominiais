<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

$env = parse_ini_file(__DIR__ . '/../../.env'); 
if (!$env) {
    echo json_encode([
        "status" => "error",
        "message" => "Não foi possível ler o arquivo de configuração .env"
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = strip_tags(trim($_POST["name"]));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST["subject"]));
    $message = trim($_POST["message"]);

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($message)) {
        echo json_encode(["status" => "error", "message" => "Preencha todos os campos corretamente."]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $env['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $env['SMTP_USER'];
        $mail->Password   = $env['SMTP_PASS'];

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        $mail->Port       = 465;

              $mail->Timeout    = 10;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 2;
        $mail->Debugoutput = 'error_log';

        $mail->setFrom($env['SMTP_USER'], 'Contato Nexcel');
        $mail->addAddress('valvares.gri@gmail.com');
        $mail->addReplyTo($email, $name);

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = "Nome: $name\nEmail: $email\nAssunto: $subject\n\nMensagem:\n$message\n";

        $mail->send();
       
        echo json_encode(["status" => "success", "message" => "Mensagem enviada com sucesso!"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Erro ao enviar: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método de envio inválido."]);
}

