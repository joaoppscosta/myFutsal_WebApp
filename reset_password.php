<?php
require_once './config.php';
require_once './core.php';
require_once './objects/User.php';

$pdo = connectDB($db);

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$message = null;
$showForm = false;

if ($token) {

    $stmt = $pdo->prepare("
        SELECT pr.*, u.user_id, u.full_name
        FROM password_resets pr
        JOIN users u ON u.user_id = pr.user_id
        WHERE pr.reset_token = :token
          AND pr.used_at IS NULL
          AND pr.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($reset) {
        $showForm = true;
    } else {
        $message = '<div class="alert alert-danger">
            Link inválido ou expirado.
        </div>';
    }
} else {
    $message = '<div class="alert alert-danger">
        Token inválido.
    </div>';
}

// Submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = filter_input(INPUT_POST, 'password');
    $confirm = filter_input(INPUT_POST, 'confirm');

    if (strlen($password) < 8) {
        $message = '<div class="alert alert-danger">
            A palavra-passe deve ter pelo menos 8 caracteres.
        </div>';
    } elseif ($password !== $confirm) {
        $message = '<div class="alert alert-danger">
            As palavras-passe não coincidem.
        </div>';
    } else {

        // Atualizar password
        $hash = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $pdo->prepare("
            UPDATE users SET password = :password
            WHERE user_id = :user_id
        ");
        $stmt->execute([
            ':password' => $hash,
            ':user_id' => $reset['user_id']
        ]);

        // Marcar token como usado
        $stmt = $pdo->prepare("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $reset['id']]);

        $message = '<div class="alert alert-success">
            Password alterada com sucesso.
            <a href="login.php">Voltar ao login</a>
        </div>';

        $showForm = false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Redefinir Password</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h4 class="mb-3">Redefinir Palavra-passe</h4>

                        <?= $message ?>

                        <?php if ($showForm): ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nova password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirmar password</label>
                                    <input type="password" name="confirm" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Alterar password
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </body>
</html>