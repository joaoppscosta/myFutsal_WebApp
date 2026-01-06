<?php

// Segurança: apenas administradores
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
    header('Location: index.php?m=admin&a=users_read&error=invalid');
    exit();
}

// Não permitir apagar o próprio utilizador
if ($user_id == $_SESSION['user_id']) {
    header('Location: index.php?m=admin&a=users_read&error=self');
    exit();
}

// Verificar se o utilizador existe
$stmt = $pdo->prepare("SELECT profile FROM users WHERE user_id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: index.php?m=admin&a=users_read&error=notfound');
    exit();
}

// Se for admin, verificar se é o último
if ($user['profile'] === 'admin') {

    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM users 
        WHERE profile = 'admin'
    ");

    $total_admins = (int) $stmt->fetchColumn();

    if ($total_admins <= 1) {
        header('Location: index.php?m=admin&a=users_read&error=last_admin');
        exit();
    }
}

// Apagar utilizador
$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);

header('Location: index.php?m=admin&a=users_read&success=deleted');
exit();
