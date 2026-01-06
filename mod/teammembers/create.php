<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// Verificar se existe equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar às Equipas</a>';
    exit();
}

$team_id = $_SESSION['current_team'];
$profile = $_SESSION['current_team_profile'];

// Apenas admin pode adicionar membros
if ($profile !== 'admin') {
    echo '<div class="alert alert-danger">Sem permissões.</div>';
    exit();
}

// Carregar Classe
require_once './objects/TeamMember.php';
$tm = new TeamMember($pdo);

// Botões
$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

if ($cancel) {
    header("Location: ?m=teammembers&a=read");
    exit();
}

if ($submit) {
    debug("Processar formulário");

    // Campos do formulário
    $user_id = filter_input(INPUT_POST, 'USER_ID', FILTER_SANITIZE_NUMBER_INT);
    $profile_new = filter_input(INPUT_POST, 'PROFILE', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $errors = false;

    if ($user_id == '') {
        ?>
        <div class="alert alert-danger">Tem que selecionar um utilizador.</div>
        <?php
        $errors = true;
    }

    if ($profile_new == '') {
        ?>
        <div class="alert alert-danger">Tem que definir um perfil.</div>
        <?php
        $errors = true;
    }

    if (!$errors) {
        debug("Informação válida — proceder ao registo na BD");

        $tm->team_id = $team_id;
        $tm->user_id = $user_id;
        $tm->profile = $profile_new;
        $tm->is_active = 1; // sempre ativo ao entrar

        if ($tm->create()) {
            ?>
            <div class="alert alert-success">Membro adicionado com sucesso.</div>
            <?php
        } else {
            ?>
            <div class="alert alert-danger">Erro ao adicionar membro.</div>
            <?php
        }
    }
}

debug("Apresentar formulário");

// Obter todos os users que ainda não pertencem à equipa
$sql = "SELECT u.user_id, u.full_name 
        FROM users u 
        WHERE u.user_id NOT IN (
            SELECT user_id FROM team_members WHERE team_id = :TID
        )";
$stmt_users = $pdo->prepare($sql);
$stmt_users->bindValue(":TID", $team_id);
$stmt_users->execute();
?>

<div class="d-flex">
    <div><h3 class="mt-4">Adicionar Membro à Equipa</h3></div>
    <div class="ms-auto">
        <a href="?m=teammembers&a=read" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<form method="POST" action="?m=teammembers&a=create">

    <label>Selecionar Utilizador</label>
    <select class="form-control" name="USER_ID">
        <option value="">-- escolher utilizador --</option>
        <?php while ($u = $stmt_users->fetch(PDO::FETCH_ASSOC)) { ?>
            <option value="<?= $u['user_id'] ?>">
                <?= htmlspecialchars($u['full_name']) ?>
            </option>
        <?php } ?>
    </select>
    <br>

    <label>Perfil</label>
    <select class="form-control" name="PROFILE">
        <option value="jogador">Jogador</option>
        <option value="admin">Administrador</option>
    </select>
    <br>

    <input type="submit" name="submit" class="btn btn-primary" value="Adicionar">
    <input type="submit" name="cancel" class="btn btn-secondary" value="Cancelar">
</form>