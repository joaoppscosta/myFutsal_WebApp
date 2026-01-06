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

// Carregar nome da equipa
$sql = "SELECT team_name FROM teams WHERE team_id = :ID";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':ID', $team_id);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo '<div class="alert alert-danger">A equipa não existe.</div>';
    exit();
}

$team_name = $row['team_name'];
$profile = $_SESSION['current_team_profile'];
?>

<div class="d-flex">
    <div><h3 class="mt-4">Dashboard da Equipa: <?= htmlspecialchars($team_name) ?></h3></div>
    <div class="ms-auto">
        <a href="?m=team&a=read" class="mt-4 btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar Atrás
        </a>
    </div>
</div>

<hr>

<?php if ($profile === 'admin'): ?>
    <div class="mt-3">
        <h5><i class="fas fa-link"></i> Convites para Utilizadores Mobile</h5>
        <a href="?m=team_invites&a=read">
            Gerir Convites <i class="fas fa-chevron-right"></i>
        </a>
    </div>
<?php endif; ?>

<div class="mt-3">
    <h5><i class="fas fa-users"></i> Membros da Equipa</h5>
    <a href="?m=teammembers&a=read&team_id=<?= $team_id ?>">
        Ver Membros <i class="fas fa-chevron-right"></i>
    </a>
</div>

<div class="mt-3">
    <h5><i class="fas fa-futbol"></i> Jogos</h5>
    <a href="?m=matches&a=read&team_id=<?= $team_id ?>">
        Ver Jogos <i class="fas fa-chevron-right"></i>
    </a>
</div>

<div class="mt-3">
    <h5><i class="fas fa-dumbbell"></i> Treinos</h5>
    <a href="?m=trainings&a=read&team_id=<?= $team_id ?>">
        Ver Treinos <i class="fas fa-chevron-right"></i>
    </a>
</div>

<div class="mt-4 p-3 border rounded bg-light">

    <?php if ($profile === 'admin'): ?>
        <p><strong>Perfil:</strong> Administrador</p>
        <p>Pode gerir membros, jogos, treinos e configurações da equipa nos botões abaixo.</p>

        <?php if ($_SESSION['current_team_profile'] === 'admin') : ?>
            <div class="mt-3 d-flex gap-2">

                <a href="?m=<?= $module ?>&a=update&id=<?= htmlspecialchars($_SESSION['current_team']) ?>"
                   class="btn btn-sm btn-outline-primary"
                   title="Editar Equipa">
                    <i class="fas fa-edit"></i> Editar Equipa
                </a>

                <a href="?m=<?= $module ?>&a=delete&id=<?= htmlspecialchars($_SESSION['current_team']) ?>"
                   class="btn btn-sm btn-outline-danger"
                   title="Apagar Equipa"
                   onclick="return confirm('Tem a certeza que deseja apagar esta equipa?');">
                    <i class="fas fa-trash"></i> Apagar Equipa
                </a>

            </div>
        <?php endif; ?>

    <?php else: ?>
        <p><strong>Perfil:</strong> Jogador</p>
        <p>Apenas pode consultar informações da equipa.</p>
    <?php endif; ?>

</div>