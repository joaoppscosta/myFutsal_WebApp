<?php
require_once './objects/MatchCallup.php';

$match_id = filter_input(INPUT_GET, 'match_id', FILTER_VALIDATE_INT);

if (!$match_id) {
    echo "<div class='alert alert-danger'>Jogo inválido.</div>";
    return;
}

$callup = new MatchCallup($pdo);
$callup->match_id = $match_id;

$stmt = $callup->readByMatch();
?>

<h2 class="mb-3">Convocatória do Jogo</h2>

<div class="d-flex mb-3">
    <div>
        <?php if ($_SESSION['current_team_profile'] === 'admin'): ?>
            <a href="?m=match_callups&a=create&match_id=<?= $match_id ?>"
               class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Convocar Jogador
            </a>
        <?php endif; ?>
    </div>

    <div class="ms-auto">
        <a href="?m=matches&a=readone&id=<?= $match_id ?>"
           class="btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar Atrás
        </a>
    </div>
</div>


<table class="table table-striped">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Titular</th>
            <th>Posição</th>
            <th>Estado</th>
            <th>Confirmado em</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['is_starter'] ? 'Sim' : 'Não' ?></td>
                <td><?= htmlspecialchars($row['position'] ?? '-') ?></td>
                <td><?= htmlspecialchars($row['confirmation_status']) ?></td>
                <td>
                    <?=
                    $row['confirmed_at'] ? htmlspecialchars($row['confirmed_at']) : '—'
                    ?>
                </td>
                <td>
                    <?php if ($_SESSION['current_team_profile'] === 'admin'): ?>
                        <a href="?m=match_callups&a=update&callup_id=<?= $row['callup_id'] ?>&match_id=<?= $match_id ?>"
                           class="btn btn-sm btn-warning">Editar</a>

                        <a href="?m=match_callups&a=delete&callup_id=<?= $row['callup_id'] ?>&match_id=<?= $match_id ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Remover jogador da convocatória?')">Remover</a>
                       <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>