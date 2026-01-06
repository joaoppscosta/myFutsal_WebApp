<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// Carregar e Instanciar Classe
require_once './objects/Team.php';
$team = new Team($pdo);
?>
<div class="d-flex">
    <div><h3 class="mt-4">Equipas | Criar Equipa</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i> 
        </a>
    </div>
</div>

<?php
$submit = filter_input(INPUT_POST, 'submit');
if ($submit) {
    debug("Processar formulário");

    // Obter valores
    $name = filter_input(INPUT_POST, 'NAME', FILTER_UNSAFE_RAW);
    $description = filter_input(INPUT_POST, 'DESCRIPTION', FILTER_UNSAFE_RAW);

    // Validar
    $errors = false;

    if ($name == '') {
        echo '<div class="alert alert-danger">Tem que definir um nome para a equipa.</div>';
        $errors = true;
    }

    if ($description == '') {
        echo '<div class="alert alert-danger">Tem que definir uma descrição para a equipa.</div>';
        $errors = true;
    }

    if (!$errors) {
        debug("Informação válida — proceder ao registo na BD");

        $team->team_name = $name;
        $team->description = $description;
        $team->created_by = $_SESSION["user_id"];

        if ($team->create()) {

            // =============== ASSOCIAR AUTOMATICAMENTE O CRIADOR À EQUIPA COMO ADMIN ===============
            $teamId = $pdo->lastInsertId();   // ID da equipa criada

            require_once './objects/TeamMember.php';
            $tm = new TeamMember($pdo);

            $tm->team_id = $teamId;
            $tm->user_id = $_SESSION['user_id'];
            $tm->profile = "admin";
            $tm->is_active = 1;

            if ($tm->create()) {
                echo '<div class="alert alert-success">Equipa criada com sucesso e associado como administrador.</div>';
            } else {
                echo '<div class="alert alert-warning">Equipa criada, mas ocorreu um erro ao associar o utilizador como administrador.</div>';
            }
            // ======================================================================================

        } else {
            echo '<div class="alert alert-danger">Erro ao criar equipa.</div>';
        }
    }
}

debug("Apresentar formulário");
?>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>">
    <input class="form-control" type="text" placeholder="ID" disabled><br>

    <input class="form-control" type="text" placeholder="Nome da Equipa" name="NAME"><br>

    <input class="form-control" type="text" placeholder="Descrição" name="DESCRIPTION"><br>

    <input type="submit" class="btn btn-primary" name="submit" value="Adicionar">
    <input type="reset" class="btn btn-secondary" value="Limpar">
</form>