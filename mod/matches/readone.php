<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

require_once './objects/TeamMatch.php';
$match = new TeamMatch($pdo);

// Verificar equipa selecionada
if (empty($_SESSION['current_team'])) {
    echo '<div class="alert alert-warning">Nenhuma equipa selecionada.</div>';
    echo '<a href="?m=team&a=read" class="btn btn-primary">Voltar</a>';
    exit();
}

$team_id = $_SESSION['current_team'];

// Obter ID do jogo
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$match->match_id = $id;
$match->readOne();

// Verificar se existe
if (empty($match->match_id)) {
    echo '<div class="alert alert-danger">Jogo não encontrado.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}

// Verificar se pertence à equipa (segurança extra)
if ($match->team_id != $team_id) {
    echo '<div class="alert alert-danger">Não tem acesso a este jogo.</div>';
    echo '<a href="?m=matches&a=read&team_id=' . $team_id . '" class="btn btn-secondary">Voltar</a>';
    exit();
}
?>

<div class="d-flex">
    <div><h3 class="mt-4">Jogos | Detalhes do Jogo</h3></div>

    <div class="ms-auto d-flex gap-2">

        <!-- Ver convocatória (todos os utilizadores) -->
        <a href="?m=match_callups&a=read&match_id=<?= $match->match_id ?>"
           class="mt-4 btn btn-outline-primary">
            <i class="fas fa-clipboard-list"></i> Ver Convocatória
        </a>

        <?php if ($_SESSION['current_team_profile'] === 'admin'): ?>

            <a href="?m=<?= $module ?>&a=update&id=<?= $id ?>"
               class="mt-4 btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>

            <a href="?m=<?= $module ?>&a=delete&id=<?= $id ?>"
               class="mt-4 btn btn-danger"
               onclick="return confirm('Tem a certeza que deseja apagar este jogo?');">
                <i class="fas fa-trash"></i> Apagar
            </a>

            <!-- Botão para editar resultado -->
            <a href="?m=<?= $module ?>&a=edit_result&id=<?= $id ?>"
               class="mt-4 btn btn-success">
                <i class="fas fa-flag-checkered"></i> Editar Resultado
            </a>

        <?php endif; ?>

        <a href="?m=<?= $module ?>&a=read&team_id=<?= $team_id ?>"
           class="mt-4 btn btn-light">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>

    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h5><i class="fas fa-info-circle"></i> Informações do Jogo</h5>
        <p><strong>Adversário:</strong> <?= htmlspecialchars($match->opponent_name) ?></p>
        <p><strong>Data:</strong> <?= htmlspecialchars($match->match_date) ?></p>
        <p><strong>Hora:</strong> <?= htmlspecialchars($match->match_time) ?></p>
        <p><strong>Local:</strong> <?= htmlspecialchars($match->location) ?></p>
        <p><strong>Tipo:</strong> <?= htmlspecialchars($match->match_type) ?></p>
        <p><strong>Casa/Fora:</strong> <?= $match->is_home ? 'Casa' : 'Fora' ?></p>
    </div>

    <div class="col-md-6">
        <h5><i class="fas fa-futbol"></i> Resultado</h5>
        <p><strong>Estado:</strong> <?= htmlspecialchars($match->result) ?></p>
        <p><strong>Golos da Equipa:</strong> <?= htmlspecialchars($match->team_goals) ?></p>
        <p><strong>Golos Sofridos:</strong> <?= htmlspecialchars($match->opponent_goals) ?></p>

        <h5 class="mt-4"><i class="fas fa-user"></i> Criado por</h5>
        <p><?= htmlspecialchars($match->creator_name ?? '—') ?></p>
        <p><strong>Data Criação:</strong> <?= htmlspecialchars($match->created_at) ?></p>
    </div>
</div>