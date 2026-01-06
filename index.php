<?php
require_once './config.php';
require_once './core.php';

session_start();
ob_start();

/**
 * Validação de sessão
 * Caso o utilizador não esteja autenticado, é redirecionado para o login
 */
if (!isset($_SESSION['user_id'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

define('DESC', 'myFutsal');
$html = '';

// Debug (útil durante o desenvolvimento)
debug('GET: ' . print_r($_GET, true));
debug('POST: ' . print_r($_POST, true));
debug('SESSION: ' . print_r($_SESSION, true));

// Carregar módulo ativo
$module = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Carregar ação
$action = filter_input(INPUT_GET, 'a', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Caso não exista módulo ou ação válidos, carregar HOME
if (!$module || !$action || !file_exists("./mod/$module/$action.php")) {
    $module = 'home';
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="<?= DESC ?>">
        <meta name="author" content="<?= AUTHOR ?>">
        <title><?= DESC ?> | <?= AUTHOR ?></title>

        <!-- Favicons -->
        <link rel="icon" href="./assets/favicon.ico">
        <link rel="icon" href="./assets/myFutsal_nobg.png" type="image/png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <!-- Bootstrap / Styles -->
        <link href="css/styles.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>

        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">

                <a class="navbar-brand" href="index.php"><b>myFutsal</b></a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">

                    <!-- Menu principal -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="?m=team&a=read">As minhas Equipas</a>
                        </li>
                    </ul>

                    <!-- Menu do utilizador -->
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <?= htmlspecialchars($_SESSION['email']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="edit_profile.php">Atualizar Dados Pessoais</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">Logout</a>
                                </li>
                            </ul>
                        </li>
                    </ul>

                </div>
            </div>
        </nav>

        <!-- CONTEÚDO -->
        <main class="container mt-4">
            <?php
            /**
             * Carregamento do conteúdo principal
             * Se não for home, carrega o módulo e ação correspondentes
             */
            $pdo = connectDB($db);

            if ($module !== 'home') {
                debug("Loading: $module/$action.php");
                require_once "./mod/$module/$action.php";
            } else {
                ?>
                <div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
                    <div class="card shadow-sm text-center" style="max-width: 420px; width: 100%;">
                        <div class="card-body">
                            <h4 class="card-title mb-3">
                                Bem-vindo/a, <?= htmlspecialchars($_SESSION['full_name']) ?>
                            </h4>

                            <p class="card-text text-muted">
                                Utilize o sistema myFutsal para gerir as suas equipas, jogos, treinos e convocatórias.
                            </p>

                            <hr>

                            <h5 class="mt-3">
                                As Minhas Equipas
                            </h5>

                            <p class="text-muted">
                                Consulte e faça a gestão das equipas onde participa.
                            </p>

                            <a href="?m=team&a=read" class="btn btn-primary mt-2">
                                Ver Equipas
                            </a>

                            <?php if (is_admin()) { ?>
                                <hr class="my-4">

                                <h5 class="text-danger">
                                    Painel de Administração
                                </h5>

                                <p class="text-muted">
                                    Área restrita para gestão da plataforma.
                                </p>

                                <div class="d-grid gap-2">
                                    <a href="?m=admin&a=users_read" class="btn btn-outline-primary">
                                        Gerir Utilizadores
                                    </a>

                                    <a href="?m=admin&a=teams_read" class="btn btn-outline-primary">
                                        Gerir Equipas
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </main>

        <!-- FOOTER -->
        <footer class="bg-light text-center py-3 mt-5">
            <small class="text-muted">
                &copy; myFutsal - Your Squad Manager <?= date("Y"); ?>
            </small>
        </footer>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    </body>
</html>

<?php
ob_end_flush();
