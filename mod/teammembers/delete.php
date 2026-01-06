<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMember.php';
$tm = new TeamMember($pdo);

// Obter ID
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=" . $_SESSION['current_team']);
    exit();
}

if ($submit) {
    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);

    $tm->member_id = $id;
    $tm->readOne(); // carrega user_id, profile, team_id

    // 1. Bloquear um admin de remover-se a si próprio da equipa
    if ($tm->user_id == $_SESSION['user_id'] && $tm->profile === 'admin') {
        ?>
        <div class="alert alert-danger">
            Não é possível remover-se a si próprio da equipa (Admin).
        </div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
        exit();
    }

    // 2. Bloquear remoção de admin se for o último admin da equipa
    if ($tm->profile === 'admin') {
        $totalAdmins = $tm->countAdminsInTeam($tm->team_id);

        if ($totalAdmins <= 1) {
            ?>
            <div class="alert alert-danger">
                Não é possível remover este administrador, pois é o único admin restante na equipa.
            </div>
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
            <?php
            exit();
        }
    }

    // --- Se chegou aqui, DELETE permitido
    if ($tm->delete()) {
        ?>
        <div class="alert alert-success">Membro removido da equipa com sucesso.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-primary">Voltar</a>
        <?php
    } else {
        ?>
        <div class="alert alert-danger">Erro ao remover membro.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
    }

} else {

    $tm->member_id = $id;
    $tm->readOne();

    // 🚫 1. Admin não pode tentar remover-se no ecrã de confirmação
    if ($tm->user_id == $_SESSION['user_id'] && $tm->profile === 'admin') {
        ?>
        <div class="alert alert-danger">
            Não é possível remover-se a si próprio da equipa (Admin).
        </div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
        exit();
    }

    // 🚫 2. Admin não pode tentar remover outro admin se for o último
    if ($tm->profile === 'admin') {
        $totalAdmins = $tm->countAdminsInTeam($tm->team_id);

        if ($totalAdmins <= 1) {
            ?>
            <div class="alert alert-danger">
                Não é possível remover este administrador, pois é o único admin restante na equipa.
            </div>
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
            <?php
            exit();
        }
    }
    ?>

    <div class="alert alert-danger">
        Tem a certeza que pretende remover <b><?= htmlspecialchars($tm->full_name) ?></b> da equipa?
    </div>

    <form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">
        <input type="hidden" name="ID" value="<?= $id ?>">

        <input type="submit" class="btn btn-danger" name="submit" value="Remover">
        <a class="btn btn-secondary" href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>">Cancelar</a>
    </form>

    <?php
}