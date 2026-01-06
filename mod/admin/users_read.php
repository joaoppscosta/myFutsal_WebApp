<?php
/**
 * ADMIN | Listar Utilizadores
 * Apenas acessível por administradores
 */
if (!is_admin()) {
    header('Location: index.php');
    exit();
}


require_once './objects/User.php';

$pdo = connectDB($db);
$userObj = new User($pdo);

// Mensagens (erro e sucesso)
$success = filter_input(INPUT_GET, 'success', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$error = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Ler todos os utilizadores
$stmt = $userObj->read();
?>

<div class="d-flex align-items-center mb-3">
    <h3 class="me-auto">Gestão de Utilizadores</h3>

    <a href="index.php" class="btn btn-light">
        <i class="fas fa-arrow-left"></i> Voltar Atrás
    </a>
</div>

<?php if ($success === 'deleted') { ?>
    <div class="alert alert-success">
        Utilizador removido com sucesso.
    </div>
<?php } ?>

<?php
if ($error) {
    switch ($error) {
        case 'self':
            echo '<div class="alert alert-danger">Não pode apagar o seu próprio utilizador.</div>';
            break;

        case 'last_admin':
            echo '<div class="alert alert-danger">Não é possível apagar o último administrador.</div>';
            break;

        case 'notfound':
            echo '<div class="alert alert-danger">Utilizador não encontrado.</div>';
            break;

        default:
            echo '<div class="alert alert-danger">Operação inválida.</div>';
    }
}
?>
<div class="card shadow-sm">
    <div class="card-body">

        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Telemóvel</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>

                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>

                    <tr>
                        <td><?= $row['user_id'] ?></td>

                        <td><?= htmlspecialchars($row['full_name']) ?></td>

                        <td><?= htmlspecialchars($row['email']) ?></td>

                        <td>
                            <span class="badge bg-<?= $row['profile'] === 'admin' ? 'danger' : 'secondary' ?>"> 
                                <?= htmlspecialchars($row['profile']) ?>
                            </span>
                        </td>

                        <td><?= htmlspecialchars($row['phone_number'] ?? '-') ?></td>

                        <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>

                        <td class="text-end">

                            <a href="index.php?m=admin&a=users_edit&id=<?= $row['user_id'] ?>"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>

                            <a href="index.php?m=admin&a=users_delete&id=<?= $row['user_id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Tem a certeza que pretende eliminar este utilizador?');">
                                <i class="fas fa-trash"></i>
                            </a>

                        </td>
                    </tr>

                <?php } ?>

            </tbody>
        </table>

    </div>
</div>