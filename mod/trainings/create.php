<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Training.php';
$training = new Training($pdo);

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}
$team_id = $_SESSION['current_team'];

// Só admin cria treinos
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para criar treinos.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="d-flex">
    <div><h3 class="mt-4">Treinos | Criar Treino</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<?php
$submit = filter_input(INPUT_POST, 'submit');
if ($submit) {
    debug("Processar formulário");

    $date = filter_input(INPUT_POST, 'DATE', FILTER_UNSAFE_RAW);
    $start = filter_input(INPUT_POST, 'START', FILTER_UNSAFE_RAW);
    $end = filter_input(INPUT_POST, 'END', FILTER_UNSAFE_RAW);
    $location = filter_input(INPUT_POST, 'LOCATION', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'DESCRIPTION', FILTER_UNSAFE_RAW);

    $errors = false;
    if ($date == '') { echo '<div class="alert alert-danger">Tem que definir a data.</div>'; $errors = true; }
    if ($start == '') { echo '<div class="alert alert-danger">Tem que definir a hora de início.</div>'; $errors = true; }
    if ($end == '')   { echo '<div class="alert alert-danger">Tem que definir a hora de fim.</div>'; $errors = true; }
    if ($location == '') { echo '<div class="alert alert-danger">Tem que definir a localização.</div>'; $errors = true; }

    if (!$errors) {
        $training->team_id = $team_id;
        $training->training_date = $date;
        $training->start_time = $start;
        $training->end_time = $end;
        $training->location = $location;
        $training->description = $description;
        $training->created_by = $_SESSION['user_id'];

        if ($training->create()) {
            echo '<div class="alert alert-success">Treino criado com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger">Erro ao criar treino.</div>';
        }
    }
}

debug("Apresentar formulário");
?>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>">
    <label>Data:</label>
    <input class="form-control" type="date" name="DATE"><br>

    <label>Hora Início:</label>
    <input class="form-control" type="time" name="START"><br>

    <label>Hora Fim:</label>
    <input class="form-control" type="time" name="END"><br>

    <label>Localização:</label>
    <input class="form-control" type="text" name="LOCATION" placeholder="Ex: Pavilhão A"><br>

    <label>Descrição:</label>
    <input class="form-control" type="text" name="DESCRIPTION" placeholder="Observações"><br>

    <input type="submit" class="btn btn-primary" name="submit" value="Adicionar">
    <input type="reset" class="btn btn-secondary" value="Limpar">
</form>