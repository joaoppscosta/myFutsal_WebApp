<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Alterar Dados Pessoais</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">

        <?php
        require_once './config.php';
        require_once './core.php';
        require_once './objects/User.php';

        session_start();

// Segurança: utilizador tem de estar autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit();
        }

        $pdo = connectDB($db);

        $user = new User($pdo);
        $user->user_id = $_SESSION['user_id'];

        $submit = filter_input(INPUT_POST, 'submit');

        if ($submit) {

            $full_name = filter_input(INPUT_POST, 'FULL_NAME', FILTER_UNSAFE_RAW);
            $email = filter_input(INPUT_POST, 'EMAIL', FILTER_SANITIZE_EMAIL);
            $phone = filter_input(INPUT_POST, 'PHONE', FILTER_UNSAFE_RAW);
            $password_current = filter_input(INPUT_POST, 'PASSWORD_CURRENT');
            $password = filter_input(INPUT_POST, 'PASSWORD');
            $password_confirm = filter_input(INPUT_POST, 'PASSWORD_CONFIRM');

            $errors = false;

            // Validações Básicas
            if ($full_name == '') {
                echo '<div class="alert alert-danger">O nome é obrigatório.</div>';
                $errors = true;
            }

            if ($email == '') {
                echo '<div class="alert alert-danger">O email é obrigatório.</div>';
                $errors = true;
            }

            // Validar password atual (obrigatória para qualquer alteração)
            if (empty($password_current)) {
                echo '<div class="alert alert-danger">
        A password atual é obrigatória para alterar os dados.
    </div>';
                $errors = true;
            } else {

                // Carregar password atual da BD
                $user->readOne();

                if (!password_verify($password_current, $user->password)) {
                    echo '<div class="alert alert-danger">
            A password atual está incorreta.
        </div>';
                    $errors = true;
                }
            }

            // Validar password apenas se for preenchida
            if (!empty($password)) {

                if (strlen($password) < 8) {
                    echo '<div class="alert alert-danger">
            A palavra-passe deve ter pelo menos 8 caracteres.
        </div>';
                    $errors = true;
                }

                if ($password !== $password_confirm) {
                    echo '<div class="alert alert-danger">
            As palavras-passe não coincidem.
        </div>';
                    $errors = true;
                }
            }

            if (!$errors) {
                $passwordChanged = !empty($password);

                $user->full_name = $full_name;
                $user->email = $email;
                $user->phone_number = $phone;

                // Password só se for preenchida
                if (!empty($password)) {
                    $user->password = $password;
                } else {
                    $user->password = null;
                }

                if ($user->update()) {

                    // Se a password foi alterada, terminar sessão e obrigar novo login
                    if ($passwordChanged) {

                        session_unset();
                        session_destroy();

                        header('Location: login.php?password_changed=1');
                        exit();
                    }

                    // Caso contrário, apenas atualizar dados da sessão
                    $user->readOne();

                    $_SESSION['full_name'] = $user->full_name;
                    $_SESSION['email'] = $user->email;

                    echo '<div class="alert alert-success">Dados atualizados com sucesso.</div>';
                } else {
                    echo '<div class="alert alert-danger">
    Não foi possível atualizar os dados.
    O email pode já estar associado a outra conta.
</div>';
                }
            }
        }

        // Garantir que o formulário mostra sempre dados atualizados
        $user->readOne();
        ?>

        <div class="d-flex">
            <div>
                <h3 class="mt-4">Alterar Dados Pessoais</h3>
            </div>

            <div class="ms-auto">
                <a href="index.php" class="mt-4 btn btn-light">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <hr>

        <div class="card shadow-sm mt-3">
            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text"
                               name="FULL_NAME"
                               class="form-control"
                               value="<?= htmlspecialchars($user->full_name) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="EMAIL"
                               class="form-control"
                               value="<?= htmlspecialchars($user->email) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telemóvel</label>
                        <input type="text"
                               name="PHONE"
                               class="form-control"
                               value="<?= htmlspecialchars($user->phone_number ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password atual</label>
                        <input type="password"
                               name="PASSWORD_CURRENT"
                               class="form-control"
                               placeholder="Introduza a sua password atual"
                               required>
                        <div class="form-text">
                            Necessária para confirmar alterações aos dados.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nova password</label>
                        <input type="password"
                               name="PASSWORD"
                               class="form-control"
                               placeholder="Deixar em branco para manter a password atual">
                        <div class="form-text">
                            Preencha apenas se pretender alterar a password.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar nova password</label>
                        <input type="password"
                               name="PASSWORD_CONFIRM"
                               class="form-control"
                               placeholder="Repita a nova password">
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                name="submit"
                                value="1"
                                class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Alterações
                        </button>

                        <button type="reset"
                                class="btn btn-secondary">
                            Limpar
                        </button>
                    </div>

                </form>

            </div>
        </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>