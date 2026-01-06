<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMember.php';
$tm = new TeamMember($pdo);

// Obter ID do membro
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$submit = filter_input(INPUT_POST, 'submit');
$cancel = filter_input(INPUT_POST, 'cancel');

// Cancelar -> Volta para trás
if ($cancel) {
    header("Location: index.php?m=$module&a=read&team_id=" . $_SESSION['current_team']);
    exit();
}

if ($submit) {

    // Carregar campos do POST
    $id = filter_input(INPUT_POST, 'ID', FILTER_SANITIZE_NUMBER_INT);
    $newProfile = filter_input(INPUT_POST, 'PROFILE', FILTER_UNSAFE_RAW);
    $newActive  = filter_input(INPUT_POST, 'ACTIVE', FILTER_SANITIZE_NUMBER_INT);

    // Carregar membro atual
    $tm->member_id = $id;
    $tm->readOne(); // carrega team_id, user_id, profile atual

    if (!$tm->member_id) {
        ?>
        <div class="alert alert-danger">Membro não encontrado.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
        exit();
    }

    // Nº total de admins ativos da equipa
    $totalAdmins = $tm->countAdminsInTeam($tm->team_id);

    // ---------------------------
    //     REGRAS DE PROTEÇÃO
    // ---------------------------

    // 1. O admin não pode alterar o seu próprio perfil
    if ($tm->user_id == $_SESSION['user_id'] && $tm->profile === 'admin') {
        if ($newProfile !== 'admin') {
            ?>
            <div class="alert alert-danger">
                Não pode alterar o seu próprio perfil de administrador.
            </div>
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
            <?php
            exit();
        }
    }

    // 2. Não permitir que o admin altere o seu perfil, se for o último admin restante na equipa
    if ($tm->profile === 'admin' && $newProfile !== 'admin' && $totalAdmins <= 1) {
        ?>
        <div class="alert alert-danger">
            Não é possível alterar o perfil deste utilizador, pois é o único administrador ativo restante.
        </div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
        exit();
    }

    // ---------------------------
    //     ATUALIZAÇÃO PERMITIDA
    // ---------------------------

    $tm->profile = $newProfile;
    $tm->is_active = $newActive;

    if ($tm->update()) {
        ?>
        <div class="alert alert-success">Registo atualizado com sucesso.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-primary">Voltar</a>
        <?php
    } else {
        ?>
        <div class="alert alert-danger">Erro ao atualizar registo.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
    }

} else {

    // ----------------------------------------
    //   MOSTRAR FORMULÁRIO PARA ATUALIZAR
    // ----------------------------------------

    $tm->member_id = $id;
    $tm->readOne();

    if (!$tm->member_id) {
        ?>
        <div class="alert alert-danger">Membro não encontrado.</div>
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Voltar</a>
        <?php
        exit();
    }

    $totalAdmins = $tm->countAdminsInTeam($tm->team_id);

    ?>

    <div class="d-flex">
        <div><h3 class="mt-4">Membros | Editar Membro</h3></div>
        <div class="ms-auto">
            <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="mt-4 btn btn-light">
                Fechar <i class="far fa-window-close"></i>
            </a>
        </div>
    </div>

    <form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&id=<?= $id ?>">

        <input class="form-control" type="text" name="ID"
               value="<?= htmlspecialchars($tm->member_id) ?>" readonly><br>

        <input class="form-control" type="text"
               value="<?= htmlspecialchars($tm->full_name) ?>" readonly><br>

        <!-- PERFIL -->
        <label class="form-label">Perfil</label>

        <?php
        // Admin a editar-se próprio -> não pode mudar de perfil (para jogador por exemplo)
        if ($tm->user_id == $_SESSION['user_id'] && $tm->profile === 'admin') {
            ?>
            <input class="form-control" type="text" value="Administrador (não pode alterar)" disabled><br>
            <input type="hidden" name="PROFILE" value="admin">
            <?php
        }

        // Outro administrador sendo o último admin -> não pode alterar perfil
        elseif ($tm->profile === 'admin' && $totalAdmins <= 1) {
            ?>
            <input class="form-control" type="text" value="Administrador (último admin — protegido)" disabled><br>
            <input type="hidden" name="PROFILE" value="admin">
            <?php
        }

        // Caso normal → mostrar select
        else {
            ?>
            <select name="PROFILE" class="form-select mb-3">
                <option value="jogador" <?= $tm->profile === 'jogador' ? 'selected' : '' ?>>Jogador</option>
                <option value="admin" <?= $tm->profile === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
            <?php
        }
        ?>

        <!-- ESTADO -->
        <label class="form-label">Estado</label>
        <select name="ACTIVE" class="form-select mb-3">
            <option value="1" <?= $tm->is_active == 1 ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= $tm->is_active == 0 ? 'selected' : '' ?>>Inativo</option>
        </select>

        <input type="submit" class="btn btn-primary" name="submit" value="Guardar Alterações">
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $_SESSION['current_team'] ?>" class="btn btn-secondary">Cancelar</a>
    </form>

    <?php
}
?>