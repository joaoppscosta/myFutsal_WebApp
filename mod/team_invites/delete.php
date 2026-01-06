<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

/**
 * Apenas administradores podem apagar convites
 */
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Acesso não autorizado.</div>';
    return;
}

require_once './objects/TeamInvite.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    echo '<div class="alert alert-danger">Convite inválido.</div>';
    return;
}

$invite = new TeamInvite($pdo);
$invite->invite_id = $id;

if ($invite->delete()) {
    header('Location: ?m=team_invites&a=read');
    exit();
} else {
    echo '<div class="alert alert-danger">Erro ao remover convite.</div>';
}