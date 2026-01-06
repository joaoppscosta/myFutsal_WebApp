<?php
require_once './config.php';
require_once './core.php';
session_start();
$loginMessage = '';
define('DESC', 'Fazer login de um utilizador');
define('UC', 'PAW');
$html = '';

// Verificar se o formulário foi submetido
debug('POST: ' . print_r($_POST, true));
$login = filter_input(INPUT_POST, 'login');
if ($login) {
    // Ligação à base de dados
    $pdo = connectDB($db);

    // Processar dados do formulário
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password');
    $password_hash_db = password_hash($password, PASSWORD_DEFAULT);
    debug("FORMULÁRIO:\n\temail: $email \n\tpwd: $password \n\thash: $password_hash_db");

    $errors = false;
    debug('Validar email.');
    if (!filter_var($email, FILTER_SANITIZE_EMAIL)) {
        $html .= '<div class="alert alert-danger">O email não é válido.</div>';
        $errors = true;
    }

    if (!$errors) {
        debug('Verificar se o email existe.');
        $sql = "SELECT * FROM `users` WHERE `email` = :EMAIL LIMIT 1";
        debug("SQL: $sql");
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                $html .= '<div class="alert alert-danger">A combinação de <b>email</b> e <b>password</b> inseridos não existem no sistema.</div>';
                $errors = true;
            } else {
                $row = $stmt->fetch();
                debug('BASE DE DADOS');
                debug('row:' . print_r($row, true));
            }
        } catch (PDOException $e) {
            $errors = true;
            $html .= '<div class="alert alert-danger">Ocorreu um erro. Por favor tente mais tarde.</div>';
            debug('PDOException: ' . $e->getMessage());
        }
    }

    if (!$errors) {
        debug('Verificar password.');
        if (!password_verify($password, $row['password'])) {
            $html .= '<div class="alert alert-danger">A combinação de <b>email</b> e <b>password</b> inseridos não existem no sistema.</div>';
            sleep(random_int(1, 3));
        } else {
            debug('LOGIN OK. Registar variáveis de sessão');
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['phone_number'] = $row['phone_number'];
            $_SESSION['profile'] = $row['profile'];
            $_SESSION['current_team'] = 0;
            $_SESSION['current_team_profile'] = null;
            $html .= '<div class="alert alert-success">Login efetuado com sucesso!  <br>Está autenticado como:  <b>' . $_SESSION['email'] . '</b><br>';
            $html .= '<a href="index.php" class="btn btn-primary">Continuar</a></div>';
        }
    }
}
if (isset($_GET['password_changed'])) {
    $loginMessage = '<div class="alert alert-info">
        Password alterada com sucesso. Faça login novamente.
    </div>';
}

debug('SESSION: ' . print_r($_SESSION, true));
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="João Costa">
        <title>myFutsal - Iniciar Sessão</title>

        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

        <!-- Favicons -->
        <link rel="icon" href="./assets/favicon.ico" sizes="any">
        <link rel="icon" href="./assets/myFutsal_nobg.png" sizes="any" type="image/svg+xml">
        <link rel="icon" href="./assets/myFutsal_nobg.png" sizes="128x128" type="image/png">
        <link rel="icon" href="./assets/myFutsal_nobg.png" sizes="32x32" type="image/png">
        <meta name="theme-color" content="#712cf9">

        <!-- Custom styles for this template -->
        <link href="css/login.css" rel="stylesheet">
    </head>
    <body class="text-center">
        <main class="form-signin w-400 m-auto">
            <img class="mb-4" src="./assets/myFutsal_nobg.png" alt="logo" height="64">
            <h1>myFutsal - Your Squad Manager</h1>
            <hr>
            <h1 class="h3 mb-3 fw-normal">Iniciar Sessão</h1>
            
            <?= $loginMessage ?>
            
            <form action="" method="POST">
                <div class="form-floating">
                    <input type="text" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required="">
                    <label for="floatingEmail">Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required="">
                    <label for="floatingPassword">Password</label>
                </div>

                <button name="login" value="login" class="w-100 btn btn-lg btn-primary" type="submit">Login</button>
            </form>
            <hr>
            <a class="btn btn-secondary" href="register.php">Registar-se</a>
            <hr>
            <a class="btn btn-secondary" href="forgot_password.php">Recuperar password</a>           
            <hr>
            <div class="container"><?= $html ?></div>

        </main>
    </body>
</html>