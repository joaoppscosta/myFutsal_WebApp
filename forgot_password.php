<?php
require_once './config.php';
require_once './core.php';
require_once './objects/User.php';

require_once './objects/PHPMailer/Exception.php';
require_once './objects/PHPMailer/PHPMailer.php';
require_once './objects/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$pdo = connectDB($db);

$message = null;

// Se existir o campo email em POST, então houve submissão
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

if ($email !== null) {

    if (!$email) {
        $message = '<div class="alert alert-danger">Introduza um email válido.</div>';
    } else {

        $user = new User($pdo);
        $user->email = $email;

        // Mensagem genérica (segurança)
        $genericMessage = '
            <div class="alert alert-success">
                Se o email existir na plataforma, irá receber instruções para recuperar a password.
            </div>
        ';

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

            $resetLink = "https://esan-tesp-ds-paw.web.ua.pt/tesp-ds-g27/myFutsal/reset_password.php?token=$token";

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
                    <p><a href='$resetLink'>Clique aqui para redefinir a sua password</a></p>
                    <p>Este link é válido por 1 hora.</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Erro silencioso (segurança, para user não ler)
            }
        }

        $message = $genericMessage;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Recuperar Password</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-3">Recuperar Palavra-passe</h4>

                        <?= $message ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Enviar instruções
                            </button>

                            <a href="login.php" class="btn btn-link">
                                Voltar ao login
                            </a>
                        </form>

                    </div>
                </div>

            </div>
        </div>

    </body>
</html>