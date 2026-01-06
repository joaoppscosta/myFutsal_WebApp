<?php
// Segurança: apenas administradores
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
    echo '<div class="alert alert-danger">Utilizador inválido.</div>';
    return;
}

// Não permitir editar o próprio utilizador (boa prática)
if ($user_id == $_SESSION['user_id']) {
    echo '<div class="alert alert-warning">
        Não é possível editar o seu próprio utilizador a partir do painel de administração.
    </div>';
    return;
}

// Carregar utilizador
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo '<div class="alert alert-danger">Utilizador não encontrado.</div>';
    return;
}

// Submissão do formulário
$submit = filter_input(INPUT_POST, 'submit');

if ($submit) {

    $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_UNSAFE_RAW));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_UNSAFE_RAW);
    $profile = filter_input(INPUT_POST, 'profile', FILTER_UNSAFE_RAW);

    $errors = false;

    if ($full_name === '') {
        echo '<div class="alert alert-danger">O nome é obrigatório.</div>';
        $errors = true;
    }

    if (!$email) {
        echo '<div class="alert alert-danger">Email inválido.</div>';
        $errors = true;
    }

    if (!in_array($profile, ['admin', 'user'])) {
        echo '<div class="alert alert-danger">Perfil inválido.</div>';
        $errors = true;
    }

    if (!$errors) {

        $stmt = $pdo->prepare("
            UPDATE users
            SET
                full_name = :full_name,
                email = :email,
                phone_number = :phone,
                profile = :profile
            WHERE user_id = :id
        ");

        $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $email,
            ':phone' => $phone ?: null,
            ':profile' => $profile,
            ':id' => $user_id
        ]);

        echo '<div class="alert alert-success">
            Utilizador atualizado com sucesso.
        </div>';

        // Recarregar dados
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container mt-4">

    <h3>Editar Utilizador</h3>
    <p class="text-muted">Gestão administrativa de utilizadores</p>

    <form method="POST" class="card shadow-sm mt-3">
        <div class="card-body">

            <div class="mb-3">
                <label class="form-label">Nome completo</label>
                <input type="text"
                       name="full_name"
                       class="form-control"
                       value="<?= htmlspecialchars($user['full_name']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email"
                       name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Telemóvel</label>
                <input type="text"
                       name="phone"
                       class="form-control"
                       value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Perfil</label>
                <select name="profile" class="form-select" required>
                    <option value="user" <?= $user['profile'] === 'user' ? 'selected' : '' ?>>
                        Utilizador
                    </option>
                    <option value="admin" <?= $user['profile'] === 'admin' ? 'selected' : '' ?>>
                        Administrador
                    </option>
                </select>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="?m=admin&a=users_read" class="btn btn-secondary">
                    Voltar
                </a>

                <button type="submit"
                        name="submit"
                        value="1"
                        class="btn btn-primary">
                    Guardar Alterações
                </button>
            </div>

        </div>
    </form>

</div>