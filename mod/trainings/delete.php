<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Training.php';
$training = new Training($pdo);

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}
$team_id = $_SESSION['current_team'];

if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para apagar treinos.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=$team_id");
    exit();
}

if ($submit) {
    $id_post = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);
    $training->training_id = $id_post;

    if ($training->delete()) {
        echo '<div class="alert alert-success">Treino removido com sucesso.</div>';
        echo '<a href="?m='.$module.'&a=read&team_id='.$team_id.'" class="btn btn-primary">Voltar</a>';
    } else {
        echo '<div class="alert alert-danger">Erro ao remover treino.</div>';
        echo '<a href="?m='.$module.'&a=read&team_id='.$team_id.'" class="btn btn-primary">Voltar</a>';
    }
    exit();
}

$training->training_id = $id;
$training->readOne();

if (empty($training->training_id) || $training->team_id != $team_id) {
    echo '<div class="alert alert-danger">Treino não encontrado ou sem acesso.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="alert alert-danger">
    Tem a certeza que pretende apagar o treino de <b><?= htmlspecialchars($training->training_date) ?></b> às <b><?= htmlspecialchars($training->start_time) ?></b>?
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">
    <input type="hidden" name="ID" value="<?= $id ?>">
    <input type="submit" class="btn btn-danger" name="submit" value="Apagar">
    <a class="btn btn-secondary" href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>">Cancelar</a>
</form>