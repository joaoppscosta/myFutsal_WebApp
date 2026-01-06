<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// ===== SEGURANÇA =====
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Não tem permissões para editar convocatórias.</div>';
    echo '<a href="?m=team&a=dashboard" class="btn btn-secondary">Voltar</a>';
    exit();
}

require_once './objects/MatchCallup.php';

$callup = new MatchCallup($pdo);

// Obter IDs
$callup_id = filter_input(INPUT_GET, 'callup_id', FILTER_VALIDATE_INT);
$match_id = filter_input(INPUT_GET, 'match_id', FILTER_VALIDATE_INT);

if (!$callup_id || !$match_id) {
    echo '<div class="alert alert-danger">Convocatória inválida.</div>';
    echo '<a href="?m=matches&a=read" class="btn btn-secondary">Voltar</a>';
    exit();
}

/* =========================
  Carregar dados atuais
  ========================= */
$sql = "
    SELECT 
        mc.callup_id,
        mc.is_starter,
        mc.position,
        mc.confirmation_status,
        u.full_name
    FROM match_callups mc
    JOIN users u ON u.user_id = mc.user_id
    WHERE mc.callup_id = :id
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $callup_id, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    echo '<div class="alert alert-danger">Convocatória não encontrada.</div>';
    exit();
}

/* =========================
  Processar formulário
  ========================= */
$submit = filter_input(INPUT_POST, 'submit');

if ($submit) {

    debug("Processar edição da convocatória");

    $is_starter = filter_input(INPUT_POST, 'is_starter', FILTER_VALIDATE_INT) ?? 0;
    $position = filter_input(INPUT_POST, 'position', FILTER_UNSAFE_RAW);
    $status = filter_input(INPUT_POST, 'confirmation_status', FILTER_UNSAFE_RAW);

    $errors = false;

    if (!$status) {
        echo '<div class="alert alert-danger">Estado da confirmação inválido.</div>';
        $errors = true;
    }

    if (!$errors) {

        $callup->callup_id = $callup_id;
        $callup->is_starter = $is_starter;
        $callup->position = $position ?: null;
        $callup->confirmation_status = $status;

        // Só define confirmed_at se não estiver pendente
        $callup->confirmed_at = ($status !== 'pendente') ? date('Y-m-d H:i:s') : null;

        if ($callup->update()) {
            echo '<div class="alert alert-success">Convocatória atualizada com sucesso.</div>';
        } else {
            echo '<div class="alert alert-danger">Erro ao atualizar convocatória.</div>';
        }
    }
}

debug("Apresentar formulário");
?>

<div class="d-flex">
    <div>
        <h3 class="mt-4">Convocatórias | Editar Convocação</h3>
        <p class="text-muted"><?= htmlspecialchars($data['full_name']) ?></p>
    </div>
    <div class="ms-auto">
        <a href="?m=match_callups&a=read&match_id=<?= $match_id ?>" class="mt-4 btn btn-light">
            Fechar <i class="far fa-window-close"></i>
        </a>
    </div>
</div>

<form method="POST" action="?m=<?= $module ?>&a=<?= $action ?>&callup_id=<?= $callup_id ?>&match_id=<?= $match_id ?>">

    <!-- Titular -->
    <div class="form-check mb-3">
        <input type="checkbox"
               name="is_starter"
               value="1"
               class="form-check-input"
               <?= $data['is_starter'] ? 'checked' : '' ?>>
        <label class="form-check-label">Titular</label>
    </div>

    <!-- Posição -->
    <div class="mb-3">
        <label class="form-label">Posição</label>
        <select name="position" class="form-select">
            <option value="">—</option>
            <?php
            $positions = ['Guarda-Redes', 'Ala', 'Fixo', 'Pivô', 'Variável'];
            foreach ($positions as $pos):
                ?>
                <option value="<?= $pos ?>"
                        <?= ($data['position'] === $pos) ? 'selected' : '' ?>>
                            <?= $pos ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Estado -->
    <div class="mb-3">
        <label class="form-label">Estado da Confirmação</label>
        <select name="confirmation_status" class="form-select" required>
            <?php
            $statuses = ['pendente', 'confirmado', 'recusado'];
            foreach ($statuses as $st):
                ?>
                <option value="<?= $st ?>"
                        <?= ($data['confirmation_status'] === $st) ? 'selected' : '' ?>>
                            <?= ucfirst($st) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <input type="submit" name="submit" value="Atualizar" class="btn btn-primary">
    <a href="?m=match_callups&a=read&match_id=<?= $match_id ?>" class="btn btn-secondary">Cancelar</a>

</form>