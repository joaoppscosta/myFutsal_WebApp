<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Training.php';
$training = new Training($pdo);

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}
$team_id = $_SESSION['current_team'];

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$training->training_id = $id;
$training->readOne();

if (empty($training->training_id)) {
    echo '<div class="alert alert-danger">Treino não encontrado.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

if ($training->team_id != $team_id) {
    echo '<div class="alert alert-danger">Não tem acesso a este treino.</div>';
    echo '<a href="?m=trainings&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="d-flex">
    <div><h3 class="mt-4">Treinos | Detalhes</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>" class="mt-4 btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
        <?php if ($_SESSION['current_team_profile'] === 'admin'): ?>
            <a href="?m=<?= $module ?>&a=update&id=<?= $id ?>" class="mt-4 btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="?m=<?= $module ?>&a=delete&id=<?= $id ?>" class="mt-4 btn btn-danger">
                <i class="fas fa-trash"></i> Apagar
            </a>
        <?php endif; ?>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <p><strong>Data:</strong> <?= htmlspecialchars($training->training_date) ?></p>
        <p><strong>Hora Início:</strong> <?= htmlspecialchars($training->start_time) ?></p>
        <p><strong>Hora Fim:</strong> <?= htmlspecialchars($training->end_time) ?></p>
        <p><strong>Local:</strong> <?= htmlspecialchars($training->location) ?></p>
    </div>
    <div class="col-md-6">
        <p><strong>Descrição:</strong> <?= htmlspecialchars($training->description) ?></p>
        <p><strong>Criado por:</strong> <?= htmlspecialchars($training->creator_name ?? '—') ?></p>
        <p><strong>Criado em:</strong> <?= htmlspecialchars($training->created_at) ?></p>
    </div>
</div>