<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Training.php';
$training = new Training($pdo);

// ID do treino
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

// Impedir acesso sem equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}
$team_id = $_SESSION['current_team'];

// Só admin edita
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para editar treinos.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=$team_id");
    exit();
}

if ($submit) {
    debug("Processar formulário");

    $id_post = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);

    $training->training_id = $id_post;
    $training->readOne();

    $date = filter_input(INPUT_POST, 'DATE', FILTER_UNSAFE_RAW);
    $start = filter_input(INPUT_POST, 'START', FILTER_UNSAFE_RAW);
    $end = filter_input(INPUT_POST, 'END', FILTER_UNSAFE_RAW);
    $location = filter_input(INPUT_POST, 'LOCATION', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'DESCRIPTION', FILTER_UNSAFE_RAW);

    $errors = false;
    if ($date == '') { echo '<div class="alert alert-danger">Tem que definir a data.</div>'; $errors = true; }
    if ($start == '') { echo '<div class="alert alert-danger">Tem que definir a hora de início.</div>'; $errors = true; }
    if ($end == '')   { echo '<div class="alert alert-danger">Tem que definir a hora de fim.</div>'; $errors = true; }

    if (!$errors) {
        $training->training_date = $date;
        $training->start_time = $start;
        $training->end_time = $end;
        $training->location = $location;
        $training->description = $description;
        $training->updated_by = $_SESSION['user_id'];

        if ($training->update()) {
            echo '<div class="alert alert-success">Treino atualizado com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger">Erro ao atualizar treino.</div>';
        }
        echo '<a href="?m='.$module.'&a=read&team_id='.$team_id.'" class="btn btn-primary">Voltar</a>';
        exit();
    }
}

// Carregar dados no formulário
$training->training_id = $id;
$training->readOne();
?>

<div class="d-flex">
    <div><h3 class="mt-4">Treinos | Editar Treino</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">
    <input type="hidden" name="ID" value="<?= htmlspecialchars($training->training_id) ?>">

    <label>Data:</label>
    <input class="form-control" type="date" name="DATE" value="<?= htmlspecialchars($training->training_date) ?>"><br>

    <label>Hora Início:</label>
    <input class="form-control" type="time" name="START" value="<?= htmlspecialchars($training->start_time) ?>"><br>

    <label>Hora Fim:</label>
    <input class="form-control" type="time" name="END" value="<?= htmlspecialchars($training->end_time) ?>"><br>

    <label>Localização:</label>
    <input class="form-control" type="text" name="LOCATION" value="<?= htmlspecialchars($training->location) ?>"><br>

    <label>Descrição:</label>
    <input class="form-control" type="text" name="DESCRIPTION" value="<?= htmlspecialchars($training->description) ?>"><br>

    <input type="submit" class="btn btn-primary" name="submit" value="Atualizar">
    <a class="btn btn-secondary" href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>">Cancelar</a>
</form>