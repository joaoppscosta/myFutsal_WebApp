<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// Carregar e instanciar Classe
require_once './objects/Team.php';
require_once './objects/TeamMember.php';

$team = new Team($pdo);
$tm = new TeamMember($pdo);
?>
<div class="d-flex">
    <div><h3 class="mt-4">Equipas | Listar Equipas</h3></div>
    <div class="ms-auto">
        <a href="?m=<?= $module ?>&a=create" class="mt-4 btn btn-primary">
            <i class="fas fa-plus-circle"></i> Nova Equipa
        </a>
    </div>
</div>

<table class="table table-striped" id="datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Logo</th>
            <th>Descrição</th>
            <th>Criado por</th>
            <th>Criado em</th>
            <th>Atualizado em</th>
            <th>Atualizado por</th>
            <th>Dashboard</th>
        </tr>
    </thead>
    <tbody>
        <?php
        debug("Mostrar apenas equipas a que o utilizador pertence.");
        $stmt = $team->readByUser($_SESSION['user_id']);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <tr>
                <td><?= htmlspecialchars($row['team_id']) ?></td>
                <td><?= htmlspecialchars($row['team_name']) ?></td>

                <td>
                    <?php if (!empty($row['logo_url'])): ?>
                        <img src="<?= htmlspecialchars($row['logo_url']) ?>"
                             alt="Logo"
                             style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= htmlspecialchars($row['creator_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['updated_at'] ?? '—') ?></td>
                <td><?= htmlspecialchars($row['updater_name'] ?? '—') ?></td>

                <td class="text-center">
                    <a href="?m=<?= $module ?>&a=select&team_id=<?= htmlspecialchars($row['team_id']) ?>"
                       class="btn btn-sm btn-outline-primary"
                       title="Entrar no Dashboard da Equipa">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </td>
            </tr>
        <?php } ?>
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