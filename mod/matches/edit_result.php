<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMatch.php';
$match = new TeamMatch($pdo);

// ID do jogo (GET)
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

// Apenas admins podem registar resultados
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para editar resultados.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($cancel) {
    header("Location: index.php?m=$module&a=readone&id=$id");
    exit();
}

// Carregar jogo
$match->match_id = $id;
$match->readOne();

if (empty($match->match_id)) {
    echo '<div class="alert alert-danger">Jogo não encontrado.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

/* --- Opcional: permitir só inserir resultado após data do jogo
// if (date('Y-m-d') < $match->match_date) {
//     echo '<div class="alert alert-warning">Não pode registar o resultado antes da data do jogo.</div>';
//     echo '<a href="?m=matches&a=readone&id=' . $id . '" class="btn btn-secondary">Voltar</a>';
//     exit();
// }
*/

if ($submit) {
    debug("Processar formulário de resultado");

    $team_goals = filter_input(INPUT_POST, 'TEAM_GOALS', FILTER_SANITIZE_NUMBER_INT);
    $opp_goals  = filter_input(INPUT_POST, 'OPP_GOALS', FILTER_SANITIZE_NUMBER_INT);
    $result     = filter_input(INPUT_POST, 'RESULT', FILTER_UNSAFE_RAW);

    $errors = false;

    // Validações básicas
    if ($team_goals === null || $team_goals === '' || !is_numeric($team_goals) || $team_goals < 0) {
        echo '<div class="alert alert-danger">Golos da equipa inválidos.</div>';
        $errors = true;
    }
    if ($opp_goals === null || $opp_goals === '' || !is_numeric($opp_goals) || $opp_goals < 0) {
        echo '<div class="alert alert-danger">Golos adversários inválidos.</div>';
        $errors = true;
    }
    if ($result == '') {
        echo '<div class="alert alert-danger">Tem que definir o estado do jogo.</div>';
        $errors = true;
    }

    if (!$errors) {
        // Aplicar alterações ao objeto
        $match->team_goals = (int)$team_goals;
        $match->opponent_goals = (int)$opp_goals;
        $match->result = $result;

        // Executar update (TeamMatch::update já atualiza estes campos)
        if ($match->update()) {
            ?>
            <div class="alert alert-success">Resultado atualizado com sucesso.</div>
            <a href="?m=<?= $module ?>&a=readone&id=<?= $id ?>" class="btn btn-primary">Ver Jogo</a>
            <?php
            // Opcional: notificar convocados / membros — ver nota abaixo
        } else {
            ?>
            <div class="alert alert-danger">Erro ao atualizar resultado.</div>
            <a href="?m=<?= $module ?>&a=readone&id=<?= $id ?>" class="btn btn-secondary">Voltar</a>
            <?php
        }

        exit();
    }
}

?>

<div class="d-flex">
    <div><h3 class="mt-4">Jogos | Editar Resultado</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=readone&id=<?= $id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">
    <input type="hidden" name="ID" value="<?= htmlspecialchars($match->match_id) ?>">

    <label>Adversário:</label>
    <input class="form-control" type="text" readonly value="<?= htmlspecialchars($match->opponent_name) ?>"><br>

    <label>Data / Hora:</label>
    <input class="form-control" type="text" readonly value="<?= htmlspecialchars($match->match_date . ' ' . $match->match_time) ?>"><br>

    <label>Golos da Equipa:</label>
    <input class="form-control" type="number" min="0" name="TEAM_GOALS" value="<?= htmlspecialchars($match->team_goals ?? 0) ?>"><br>

    <label>Golos Adversário:</label>
    <input class="form-control" type="number" min="0" name="OPP_GOALS" value="<?= htmlspecialchars($match->opponent_goals ?? 0) ?>"><br>

    <label>Estado do Jogo:</label>
    <select class="form-control" name="RESULT">
        <option value="pendente" <?= $match->result === 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="finalizado" <?= $match->result === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
        <option value="adiado" <?= $match->result === 'adiado' ? 'selected' : '' ?>>Adiado</option>
        <option value="cancelado" <?= $match->result === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
    </select>
    <br>

    <input type="submit" class="btn btn-primary" name="submit" value="Guardar Resultado">
    <a class="btn btn-secondary" href="?m=<?= $module ?>&a=readone&id=<?= $id ?>">Cancelar</a>
</form>