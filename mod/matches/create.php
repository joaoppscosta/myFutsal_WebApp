<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMatch.php';
$match = new TeamMatch($pdo);

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}

$team_id = $_SESSION['current_team'];

// Verificar permissões (apenas ADMIN pode criar novos registos)
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para criar jogos.</div>';
    echo '<a href="?m=matches&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="d-flex">
    <div><h3 class="mt-4">Jogos | Criar Jogo</h3></div>
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

    // Obter valores
    $opponent = filter_input(INPUT_POST, 'OPPONENT', FILTER_UNSAFE_RAW);
    $date = filter_input(INPUT_POST, 'DATE', FILTER_UNSAFE_RAW);
    $time = filter_input(INPUT_POST, 'TIME', FILTER_UNSAFE_RAW);
    $location = filter_input(INPUT_POST, 'LOCATION', FILTER_UNSAFE_RAW);
    $match_type = filter_input(INPUT_POST, 'TYPE', FILTER_UNSAFE_RAW);
    $is_home = filter_input(INPUT_POST, 'HOME', FILTER_SANITIZE_NUMBER_INT);

    $errors = false;

    // Validações
    if ($opponent == '') {
        echo '<div class="alert alert-danger">Tem que definir o adversário.</div>';
        $errors = true;
    }

    if ($date == '') {
        echo '<div class="alert alert-danger">Tem que definir a data do jogo.</div>';
        $errors = true;
    }

    if ($time == '') {
        echo '<div class="alert alert-danger">Tem que definir a hora do jogo.</div>';
        $errors = true;
    }

    if ($location == '') {
        echo '<div class="alert alert-danger">Tem que definir a localização.</div>';
        $errors = true;
    }

    if (!$errors) {
        debug("Informação válida — proceder ao registo na BD");

        // Preencher objeto
        $match->team_id = $team_id;
        $match->opponent_name = $opponent;
        $match->match_date = $date;
        $match->match_time = $time;
        $match->location = $location;
        $match->match_type = $match_type;
        $match->is_home = $is_home;
        $match->created_by = $_SESSION["user_id"];

        if ($match->create()) {
            echo '<div class="alert alert-success">Jogo criado com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger">Erro ao criar jogo.</div>';
        }
    }
}

debug("Apresentar formulário");
?>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>">
    <input class="form-control" type="text" placeholder="Adversário" name="OPPONENT"><br>

    <label>Data:</label>
    <input class="form-control" type="date" name="DATE"><br>

    <label>Hora:</label>
    <input class="form-control" type="time" name="TIME"><br>

    <input class="form-control" type="text" placeholder="Localização" name="LOCATION"><br>

    <label>Tipo de Jogo:</label>
    <select class="form-control" name="TYPE">
        <option value="pendente">Pendente</option>
        <option value="campeonato">Campeonato</option>
        <option value="amigavel">Amigável</option>
        <option value="treino">Treino</option>
    </select>
    <br>

    <label>Casa ou Fora?</label>
    <select class="form-control" name="HOME">
        <option value="1">Casa</option>
        <option value="0">Fora</option>
    </select>
    <br>

    <input type="submit" class="btn btn-primary" name="submit" value="Adicionar">
    <input type="reset" class="btn btn-secondary" value="Limpar">
</form>