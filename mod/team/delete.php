<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Team.php';
$team = new Team($pdo);


// Verificar se existe equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}

// Apenas admins da equipa podem apagar
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para apagar esta equipa.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}

// ID da equipa vem SEMPRE da sessão
$id = (int) $_SESSION['current_team'];

?>
<div class="d-flex">
    <div><h3 class="mt-4">Equipas | Eliminar Equipa</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<?php
// ID da equipa a apagar
$id = (int) $_SESSION['current_team'];

// Botões
$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

if ($cancel) {
    header("Location: index.php?m=$module&a=read");
    exit();
}

if ($submit) {

    // ID escondido do POST
    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);

    $team->team_id = $id;

    // Eliminar equipa
    if ($team->delete()) {
        ?>
        <div class="alert alert-success">Registo eliminado com sucesso</div>
        <?php
    } else {
        ?>
        <div class="alert alert-danger">Erro ao eliminar registo</div>
        <?php
    }

} else {

    debug("Apresentar formulário");

    $team->team_id = $id;
    $team->readOne();

    ?>
    <div class="alert alert-danger">
        Deseja mesmo eliminar a equipa <b><?= htmlspecialchars($team->team_name) ?></b>?
    </div>

    <form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>">
        <input class="form-control" type="hidden" name="ID" value="<?= htmlspecialchars($team->team_id) ?>"><br>
        <input type="submit" class="btn btn-primary" name="submit" value="Eliminar">
        <a class="btn btn-secondary" href="?m=<?= $module ?>&a=read">Cancelar</a>
    </form>
    <?php
}
?>