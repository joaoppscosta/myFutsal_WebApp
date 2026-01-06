<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamInvite.php';

if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Acesso negado.</div>';
    exit();
}

$invite = new TeamInvite($pdo);

$submit = filter_input(INPUT_POST, 'submit');

if ($submit) {

    $token = bin2hex(random_bytes(16));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $invite->team_id = $_SESSION['current_team'];
    $invite->token = $token;
    $invite->created_by = $_SESSION['user_id'];
    $invite->expires_at = $expiresAt;

    if ($invite->create()) {
        echo '<div class="alert alert-success">
                Convite criado com sucesso.<br>
                <strong>Token:</strong> ' . htmlspecialchars($token) . '
              </div>';
    } else {
        echo '<div class="alert alert-danger">Erro ao criar convite.</div>';
    }
}
?>

<h3 class="mt-4">Gerar Convite para Mobile</h3>

<form method="POST" action="?m=team_invites&a=create">
    <div class="d-flex align-items-center gap-2 mt-3">

        <button type="submit" name="submit" value="1" class="btn btn-primary">
            <i class="fas fa-ticket-alt"></i> Gerar Token
        </button>

        <a href="?m=team_invites&a=read" class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>

    </div>
</form>