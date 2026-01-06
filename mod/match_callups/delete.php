<?php

// ===== SEGURANÇA =====
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para remover convocatórias.</div>';
    echo '<a href="?m=team&a=dashboard" class="btn btn-secondary">Voltar</a>';
    exit();
}

require_once './objects/MatchCallup.php';

$callup_id = filter_input(INPUT_GET, 'callup_id', FILTER_VALIDATE_INT);
$match_id = filter_input(INPUT_GET, 'match_id', FILTER_VALIDATE_INT);

if ($callup_id) {
    $callup = new MatchCallup($pdo);
    $callup->callup_id = $callup_id;
    $callup->delete();
}

header("Location: ?m=match_callups&a=read&match_id=$match_id");
exit();
