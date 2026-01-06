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

// Apenas admins podem editar equipa
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para editar esta equipa.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>
<div class="d-flex">
    <div><h3 class="mt-4">Equipas | Editar Equipa</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>
<?php

// ID da equipa a atualizar
$id = (int) $_SESSION['current_team'];

// Botões
$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

if ($cancel) {
    header("Location: index.php?m=$module&a=read");
    exit();
}

if ($submit) {
    debug("Processar formulário");

    // Verificar dados do formulário
    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);
    $name = filter_input(INPUT_POST, 'NAME', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'DESCRIPTION', FILTER_UNSAFE_RAW);

    $errors = false;

    if ($name == '') {
        ?>
        <div class="alert alert-danger">Tem que definir um nome.</div>
        <?php
        $errors = true;
    }
    if ($description == '') {
        ?>
        <div class="alert alert-danger">Tem que definir uma descrição.</div>
        <?php
        $errors = true;
    }

    if (!$errors) {
        debug('Informação válida proceder ao registo na BD');

        // Carregar registo atual
        $team->team_id = $id;
        $team->readOne();

        // Aplicar alterações
        $team->team_name = $name;
        $team->description = $description;

        // Atualizar updated_by com o ID na variável de sessão
        if (!empty($_SESSION['user_id'])) {
            $team->updated_by = $_SESSION['user_id'];
        }

        // Atualizar equipa
        if ($team->update()) {
            ?>
            <div class="alert alert-success">Registo atualizado com sucesso</div>
            <?php
        } else {
            ?>
            <div class="alert alert-danger">Erro ao atualizar registo</div>
            <?php
        }
    }
}

debug("Apresentar formulário");

// Carregar registo atual
$team->team_id = $id;
$team->readOne();
?>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>">
    <input class="form-control" type="text" name="ID" readonly 
           value="<?= htmlspecialchars($team->team_id) ?>" placeholder="ID"><br>

    <input class="form-control" type="text" name="NAME" 
           value="<?= htmlspecialchars($team->team_name) ?>" placeholder="Nome da Equipa"><br>

    <input class="form-control" type="text" name="DESCRIPTION"
           value="<?= htmlspecialchars($team->description) ?>" placeholder="Descrição"><br>

    <input type="submit" class="btn btn-primary" name="submit" value="Atualizar">
    <a class="btn btn-secondary" href="?m=<?= $module ?>&a=read">Voltar</a>
</form>