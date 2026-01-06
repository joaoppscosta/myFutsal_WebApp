<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

// Garante que há uma equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar às Equipas</a>';
    exit();
}

$team_id = $_SESSION['current_team'];
$profile = $_SESSION['current_team_profile'];

// Carregar Classe TeamMember
require_once './objects/TeamMember.php';
$tm = new TeamMember($pdo);

// Obter nome da equipa
$sql = "SELECT team_name FROM teams WHERE team_id = :ID";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':ID', $team_id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$team_name = $row ? $row['team_name'] : "Equipa desconhecida";
?>

<div class="d-flex">
    <div><h3 class="mt-4">Membros da Equipa: <?= htmlspecialchars($team_name) ?></h3></div>
    <div class="ms-auto">
        <a href="?m=team&a=dashboard" class="mt-4 btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
    </div>
</div>

<hr>

<?php if ($profile === 'admin'): ?>
    <a class="btn btn-primary mb-3" href="?m=teammembers&a=create">
        <i class="fas fa-user-plus"></i> Adicionar Membro
    </a>
<?php endif; ?>

<table class="table table-striped" id="datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Perfil</th>
            <th>Data Entrada</th>
            <th>Ativo</th>
            <th>Operações</th>
        </tr>
    </thead>
    <tbody>

        <?php
        $stmt = $tm->read($team_id);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <tr>
                <td><?= htmlspecialchars($row['member_id']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['profile']) ?></td>
                <td><?= htmlspecialchars($row['join_date']) ?></td>
                <td><?= $row['is_active'] ? "Sim" : "Não" ?></td>

                <td>
                    <?php if ($profile === 'admin'): ?>
                        <a href="?m=teammembers&a=update&id=<?= $row['member_id'] ?>" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>

                        <a href="?m=teammembers&a=delete&id=<?= $row['member_id'] ?>" title="Remover">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
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