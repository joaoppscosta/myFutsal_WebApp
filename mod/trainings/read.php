<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/Training.php';
$training = new Training($pdo);

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar às Equipas</a>';
    exit();
}
$team_id = $_SESSION['current_team'];
?>
<div class="d-flex">
    <div><h3 class="mt-4">Treinos | Lista de Treinos</h3></div>
    <div class="ms-auto">
        <a href="?m=team&a=dashboard" class="mt-4 btn btn-light">
                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
            </a>
        <?php if (isset($_SESSION['current_team_profile']) && $_SESSION['current_team_profile'] === 'admin'): ?>
            <a href="?m=<?= $module ?>&a=create&team_id=<?= htmlspecialchars($team_id) ?>" class="mt-4 btn btn-primary">
                <i class="fas fa-plus-circle"></i> Novo Treino
            </a>
        <?php endif; ?>
    </div>
</div>

<table class="table table-striped" id="datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Data</th>
            <th>Início</th>
            <th>Fim</th>
            <th>Local</th>
            <th>Descrição</th>
            <th>Criado por</th>
            <th>Criado em</th>
            <th>Operações</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $training->readByTeam($team_id);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
            <tr>
                <td><?= htmlspecialchars($row['training_id']) ?></td>
                <td><?= htmlspecialchars($row['training_date']) ?></td>
                <td><?= htmlspecialchars($row['start_time']) ?></td>
                <td><?= htmlspecialchars($row['end_time']) ?></td>
                <td><?= htmlspecialchars($row['location']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['creator_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="?m=<?= $module ?>&a=readone&id=<?= htmlspecialchars($row['training_id']) ?>" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<script>
    window.addEventListener('DOMContentLoaded', event => {
        const datatablesSimple = document.getElementById('datatable');
        if (datatablesSimple) {
            new simpleDatatables.DataTable(datatablesSimple);
        }
    });
</script>