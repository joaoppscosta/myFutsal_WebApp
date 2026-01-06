<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMatch.php';
$match = new TeamMatch($pdo);

// Obter ID do jogo
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}

$team_id = $_SESSION['current_team'];

// Apenas admin pode apagar
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para apagar jogos.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=$team_id");
    exit();
}

if ($submit) {

    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);

    $match->match_id = $id;

    if ($match->delete()) {
        ?>
        <div class="alert alert-success">Jogo removido com sucesso.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="btn btn-primary">Voltar</a>
        <?php
    } else {
        ?>
        <div class="alert alert-danger">Erro ao remover jogo.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="btn btn-secondary">Voltar</a>
        <?php
    }
    exit();
}

// Carregar dados do jogo para confirmar
$match->match_id = $id;
$match->readOne();

if (empty($match->match_id)) {
    echo '<div class="alert alert-danger">Jogo não encontrado.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="alert alert-danger">
    Tem a certeza que pretende apagar o jogo contra
    <b><?= htmlspecialchars($match->opponent_name) ?></b> em
    <b><?= htmlspecialchars($match->match_date) ?></b>?
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">
    <input type="hidden" name="ID" value="<?= $id ?>">

    <input type="submit" class="btn btn-danger" name="submit" value="Apagar">
    <a class="btn btn-secondary" href="?m=<?= $module ?>&a=readone&id=<?= $id ?>">Cancelar</a>
</form>