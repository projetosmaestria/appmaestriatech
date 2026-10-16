<?php
require_once 'application/third_party/phpmailer/PHPMailerAutoload.php';

$mail = new PHPMailer;

$mail->isSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.maestriatech.com.br';
$mail->SMTPAuth = true;
$mail->Username = 'contato=maestriatech.com.br';
$mail->Password = 'hHpoMXtDE33S9K@T';
$mail->SMTPSecure = 'ssl';  // Usar SSL em vez de TLS
$mail->Port = 465;           // Porta SSL

$mail->setFrom('contato@maestriatech.com.br', 'MaestriaTech Test');
$mail->addAddress('thiago@maestriatech.com.br');

$mail->isHTML(true);
$mail->Subject = 'Teste de Envio de E-mail';
$mail->Body    = 'Este é um teste para verificar a configuração de SMTP.';

if (!$mail->send()) {
    echo 'Erro ao enviar e-mail: ' . $mail->ErrorInfo;
} else {
    echo 'E-mail enviado com sucesso!';
}
