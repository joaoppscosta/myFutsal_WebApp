<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// ===== SEGURANÇA =====
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para convocar jogadores.</div>';
    echo '<a href="?m=team&a=dashboard" class="btn btn-secondary">Voltar</a>';
    exit();
}
// Carregar classe
require_once './objects/MatchCallup.php';

// Validar match_id
$match_id = filter_input(INPUT_GET, 'match_id', FILTER_VALIDATE_INT);

if (!$match_id) {
    echo '<div class="alert alert-danger">Jogo inválido.</div>';
    echo '<a href="?m=matches&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}

/* =========================================================
   Obter equipa associada ao jogo
========================================================= */
$sql = "
    SELECT team_id
    FROM matches
    WHERE match_id = :match_id
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':match_id', $match_id);
$stmt->execute();
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    echo '<div class="alert alert-danger">Jogo não encontrado.</div>';
    exit();
}

$team_id = $match['team_id'];

/* =========================================================
   Obter membros ativos da equipa
========================================================= */
$sql = "
    SELECT u.user_id, u.full_name
    FROM team_members tm
    JOIN users u ON u.user_id = tm.user_id
    WHERE tm.team_id = :team_id
      AND tm.is_active = 1
    ORDER BY u.full_name
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':team_id', $team_id);
$stmt->execute();
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   Processar formulário
========================================================= */
$submit = filter_input(INPUT_POST, 'submit');

if ($submit) {
    debug("Processar formulário de convocatória");

    $users = filter_input(INPUT_POST, 'users', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $errors = false;

    if (empty($users)) {
        echo '<div class="alert alert-danger">Tem de selecionar pelo menos um jogador.</div>';
        $errors = true;
    }

    if (!$errors) {
        debug("Criar convocatórias");

        foreach ($users as $user_id) {

            $callup = new MatchCallup($pdo);
            $callup->match_id = $match_id;
            $callup->user_id = (int)$user_id;

            // Evitar duplicados
            if (!$callup->exists()) {
                $callup->create();
            }
        }

        echo '<div class="alert alert-success">Convocatória criada com sucesso.</div>';
        echo '<a href="?m=match_callups&a=read&match_id=' . $match_id . '" class="btn btn-primary mt-2">
                Ver Convocatória
              </a>';
        return;
    }
}

debug("Apresentar formulário");
?>

<div class="d-flex">
    <div><h3 class="mt-4">Convocatórias | Convocar Jogadores</h3></div>
    <div class="ms-auto">
        <a href="?m=match_callups&a=read&match_id=<?= $match_id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<hr>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&match_id=<?= $match_id ?>">

    <h5>Selecionar Jogadores</h5>
    <p class="text-muted">Selecione os jogadores a convocar para este jogo.</p>

    <?php foreach ($members as $member): ?>
        <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   name="users[]"
                   value="<?= $member['user_id'] ?>"
                   id="user<?= $member['user_id'] ?>">

            <label class="form-check-label" for="user<?= $member['user_id'] ?>">
                <?= htmlspecialchars($member['full_name']) ?>
            </label>
        </div>
    <?php endforeach; ?>

    <div class="mt-4">
        <input type="submit" class="btn btn-success" name="submit" value="Criar Convocatória">
        <input type="reset" class="btn btn-secondary" value="Limpar">
    </div>

</form>