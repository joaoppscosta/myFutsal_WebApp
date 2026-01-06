<?php

require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';

require_once '../../objects/PHPMailer/Exception.php';
require_once '../../objects/PHPMailer/PHPMailer.php';
require_once '../../objects/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Content-Type: application/json; charset=UTF-8");

$pdo = connectDB($db);
$user = new User($pdo);

// Ler JSON
$data = json_decode(file_get_contents("php://input"));

if (!$data || empty($data->email)) {
    http_response_code(400);
    echo json_encode(["error" => "Pedido sem informação"]);
    exit();
}

// Sanitizar email
$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);

// Mensagem genérica (segurança)
$genericResponse = [
    "message" => "Se o email existir na plataforma, irá receber instruções para recuperar a password."
];

if (!$email) {
    http_response_code(200);
    echo json_encode($genericResponse);
    exit();
}

$user->email = $email;

// Só processa internamente se o email existir
if ($user->emailExists()) {

    // Gerar token seguro
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Invalidar pedidos anteriores
    $stmt = $pdo->prepare("
        UPDATE password_resets
        SET used_at = NOW()
        WHERE user_id = :user_id
          AND used_at IS NULL
    ");
    $stmt->execute([':user_id' => $user->user_id]);

    // Criar novo pedido
    $stmt = $pdo->prepare("
        INSERT INTO password_resets (user_id, reset_token, expires_at)
        VALUES (:user_id, :token, :expires)
    ");
    $stmt->execute([
        ':user_id' => $user->user_id,
        ':token' => $token,
        ':expires' => $expires
    ]);

    // Link para reset (web)
    $resetLink = "https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g27/myFutsal/reset_password.php?token=$token";

    // Enviar email
    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = EMAIL_SMTPAUTH;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = EMAIL_SECURITY;
        $mail->Port = EMAIL_PORT;

        $mail->CharSet = EMAIL_CHARSET;
        $mail->Encoding = EMAIL_ENCODING;

        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($user->email, $user->full_name);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperação de Password - myFutsal';

        $mail->Body = "
            <p>Olá <strong>{$user->full_name}</strong>,</p>
            <p>Recebemos um pedido para redefinir a sua palavra-passe.</p>
            <p>
                <a href='$resetLink'>
                    Clique aqui para redefinir a sua password
                </a>
            </p>
            <p>Este link é válido por 1 hora.</p>
        ";

        $mail->send();

    } catch (Exception $e) {
        // Erro silencioso (não revelar)
    }
}

// Resposta sempre genérica
http_response_code(200);
echo json_encode($genericResponse);