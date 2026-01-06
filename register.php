<?php
require_once './config.php';
require_once './core.php';
define('DESC', 'Registar um novo utilizador');
define('UC', 'PAW');
$html = '';

// Verificar se o formulário foi submetido
debug('POST: ' . print_r($_POST, true));
$register = filter_input(INPUT_POST, 'register');
if ($register) {
    // Ligação à base de dados
    $pdo = connectDB($db);

    // Processar dados do formulário
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $password_confirmar = filter_input(INPUT_POST, 'password_confirmar', FILTER_UNSAFE_RAW);
    $password_hash_db = password_hash($password, PASSWORD_ARGON2ID);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_UNSAFE_RAW);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_NUMBER_INT);

    debug("FORMULÁRIO:\n\temail: $email \n\tpwd: $password \n\thash: $password_hash_db \n\tfull_name: $full_name \n\tphone_number: $phone_number");

    $errors = false;
    debug('Validar email.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $html .= '<div class="alert alert-danger">O email não é válido.</div>';
        $errors = true;
    }
    
    debug('Validar password');
    if (strlen($password) < 8) {
        $html .= '<div class="alert alert-danger">Palavra-passe tem menos de 8 caracteres.</div>';
        $errors = true;
    }
    
    debug('Validar confirmação de password');
    if ($password != $password_confirmar) {
        $html .= '<div class="alert alert-danger">As palavras-passe têm de ser iguais.</div>';
        $errors = true;
    }

    debug('Verificar se email já está registado.');
    $sql = "SELECT user_id FROM users WHERE email = :EMAIL LIMIT 1";
    debug("SQL: $sql");
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $html .= '<div class="alert alert-danger">O email indicado já se encontra registado.</div>';
            $errors = true;
        }
    } catch (PDOException $e) {
        $html .= '<div class="alert alert-danger">Ocorreu um erro. Por favor tente mais tarde.</div>';
        debug('PDOException: ' . $e->getMessage());
    }

    if (!$errors) {
        debug('Informação válida proceder ao registo.');
        $sql = "INSERT INTO users(email,password,full_name,phone_number) VALUES(:EMAIL,:PASSWORD,:FULL_NAME,:PHONE_NUMBER)";
        debug("SQL: $sql");
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":EMAIL", $email, PDO::PARAM_STR);
            $stmt->bindValue(":PASSWORD", $password_hash_db, PDO::PARAM_STR);
            $stmt->bindValue(":FULL_NAME", $full_name, PDO::PARAM_STR);
            $stmt->bindValue(":PHONE_NUMBER", $phone_number, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $html .= '<div class="alert  alert-success">Utilizador criado com sucesso! '
                        . '<a class="btn btn-primary" href="./login.php">Login</a></div>';
            } else {
                $html .= '<div class="alert  alert-danger">Erro ao inserir na Base de Dados.</div>';
            }
        } catch (PDOException $e) {
            $html .= '<div class="alert alert-danger">Ocorreu um erro. Por favor tente mais tarde.</div>';
            debug('PDOException: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="João Costa">
        <title>Registo de novo utilizador</title>

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
        <link href="css/registo.css" rel="stylesheet">
    </head>
    <body class="text-center">

        <main class="form-signin w-100 m-auto">
            <form method="post" action="">
                <img class="mb-4" src="./assets/myFutsal_nobg.png" alt="logo" height="64">
                <h1>myFutsal - Your Squad Manager</h1>
                <hr>
                <h1 class="h3 mb-3 fw-normal">Registo de novo utilizador</h1>

                <div class="form-floating">
                    <input type="text" name="full_name" class="form-control" id="floatingNome" placeholder="nome completo" required="">
                    <label for="floatingInput">Nome Completo</label>
                </div>
                <div class="form-floating">
                    <input type="number" name="phone_number" class="form-control" id="floatingInput" placeholder="nº de telemóvel" required="">
                    <label for="floatingInput">Nº Telemóvel</label>
                </div>
                <div class="form-floating">
                    <input type="email" name="email" class="form-control" id="floatingInput" placeholder="name@example.com" required="">
                    <label for="floatingInput">Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required="">
                    <label for="floatingPassword">Password</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password_confirmar" class="form-control" id="floatingPasswordConfirmar" placeholder="Password" required="">
                    <label for="floatingPassword">Confirmar a Password</label>
                </div>

                <div class="checkbox mb-3">
                    <label>
                        <input type="checkbox" id="concordo" value="concordo" name="concordo" required=""> Concordo com os <a href="#">termos de utilização</a>
                    </label>
                </div>
                <button class="w-100 btn btn-lg btn-primary" type="submit" name="register" value="register">Registar</button>
            </form>
            <div><?= $html ?></div>
            <hr>
            <a class="btn btn-secondary" href="login.php">Login</a>
        </main>

    </body>
</html>