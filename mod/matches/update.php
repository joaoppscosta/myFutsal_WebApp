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

// Impedir acesso sem equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}

$team_id = $_SESSION['current_team'];

// Apenas admins podem editar jogos
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para editar jogos.</div>';
    echo '<a href="?m=matches&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=$team_id");
    exit();
}

if ($submit) {

    debug("Processar formulário");

    // ID escondido
    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);

    // Carregar dados atuais (garantia)
    $match->match_id = $id;
    $match->readOne();

    // Obter novos valores
    $opponent   = filter_input(INPUT_POST, 'OPPONENT', FILTER_UNSAFE_RAW);
    $date       = filter_input(INPUT_POST, 'DATE', FILTER_UNSAFE_RAW);
    $time       = filter_input(INPUT_POST, 'TIME', FILTER_UNSAFE_RAW);
    $location   = filter_input(INPUT_POST, 'LOCATION', FILTER_UNSAFE_RAW);
    $match_type = filter_input(INPUT_POST, 'TYPE', FILTER_UNSAFE_RAW);
    $is_home    = filter_input(INPUT_POST, 'HOME', FILTER_SANITIZE_NUMBER_INT);

    $errors = false;

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

        // Aplicar alterações ao objeto
        $match->opponent_name = $opponent;
        $match->match_date    = $date;
        $match->match_time    = $time;
        $match->location      = $location;
        $match->match_type    = $match_type;
        $match->is_home       = $is_home;

        if ($match->update()) {
            ?>
            <div class="alert alert-success">Jogo atualizado com sucesso.</div>
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="btn btn-primary">Voltar</a>
            <?php
        } else {
            ?>
            <div class="alert alert-danger">Erro ao atualizar jogo.</div>
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="btn btn-secondary">Voltar</a>
            <?php
        }
        exit();
    }
}

// Carregar dados atuais no formulário
$match->match_id = $id;
$match->readOne();
?>

<div class="d-flex">
    <div><h3 class="mt-4">Jogos | Editar Jogo</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=readone&id=<?= $id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">

    <input type="hidden" name="ID" value="<?= htmlspecialchars($match->match_id) ?>">

    <label>Adversário:</label>
    <input class="form-control" type="text" name="OPPONENT"
           value="<?= htmlspecialchars($match->opponent_name) ?>"><br>

    <label>Data:</label>
    <input class="form-control" type="date" name="DATE"
           value="<?= htmlspecialchars($match->match_date) ?>"><br>

    <label>Hora:</label>
    <input class="form-control" type="time" name="TIME"
           value="<?= htmlspecialchars($match->match_time) ?>"><br>

    <label>Localização:</label>
    <input class="form-control" type="text" name="LOCATION"
           value="<?= htmlspecialchars($match->location) ?>"><br>

    <label>Tipo de Jogo:</label>
    <select class="form-control" name="TYPE">
        <option value="pendente"   <?= $match->match_type === 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="campeonato" <?= $match->match_type === 'campeonato' ? 'selected' : '' ?>>Campeonato</option>
        <option value="amigavel"   <?= $match->match_type === 'amigavel' ? 'selected' : '' ?>>Amigável</option>
        <option value="treino"     <?= $match->match_type === 'treino' ? 'selected' : '' ?>>Treino</option>
    </select><br>

    <label>Casa ou Fora?</label>
    <select class="form-control" name="HOME">
        <option value="1" <?= $match->is_home ? 'selected' : '' ?>>Casa</option>
        <option value="0" <?= !$match->is_home ? 'selected' : '' ?>>Fora</option>
    </select>
    <br>

    <input type="submit" class="btn btn-primary" name="submit" value="Atualizar">
    <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="btn btn-secondary">Cancelar</a>
</form>