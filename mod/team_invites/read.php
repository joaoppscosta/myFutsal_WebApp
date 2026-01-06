<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

/**
 * Apenas administradores podem gerir convites
 */
if ($_SESSION['current_team_profile'] !== 'admin') {
    echo '<div class="alert alert-danger">Acesso não autorizado.</div>';
    return;
}

/**
 * Verificar equipa selecionada
 */
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    return;
}

$team_id = $_SESSION['current_team'];
?>

<div class="d-flex">
    <div><h3 class="mt-4">Convites para Mobile</h3></div>
    <div class="ms-auto">
        <a href="?m=team&a=dashboard" class="mt-4 btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
        </a>
        <a href="?m=team_invites&a=create" class="mt-4 btn btn-primary">
            <i class="fas fa-plus"></i> Gerar Convite
        </a>
    </div>
</div>

<hr>

<?php
/**
 * Obter convites da equipa
 */
$sql = "
    SELECT *
    FROM team_invites
    WHERE team_id = :TEAM
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':TEAM', $team_id, PDO::PARAM_INT);
$stmt->execute();
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Token</th>
            <th>Criado em</th>
            <th>Expira em</th>
            <th>Usado</th>
            <th>Operações</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td>
                    <code><?= htmlspecialchars($row['token']) ?></code>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td><?= htmlspecialchars($row['expires_at'] ?? '—') ?></td>
                <td><?= $row['used_at'] ? 'Sim' : 'Não' ?></td>
                <td>
                    <a href="?m=team_invites&a=delete&id=<?= $row['invite_id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Deseja revogar este convite?')">
                        <i class="fas fa-trash"></i> Revogar
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>