<?php

require_once '../../config.php';
require_once '../../core.php';

$pdo = connectDB($db);

require_once '../../objects/User.php';
$user = new User($pdo);

header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Pedido sem informação"]);
    exit();
}

// Validar dados
$user->email = isset($data->email)
    ? filter_var($data->email, FILTER_VALIDATE_EMAIL)
    : '';

$user->password = isset($data->password)
    ? filter_var($data->password, FILTER_UNSAFE_RAW)
    : '';

$user->full_name = isset($data->full_name)
    ? trim($data->full_name)
    : '';

$user->phone_number = isset($data->phone_number)
    ? filter_var($data->phone_number, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
    : '';
$user->profile = 'user';

$error = '';

// Email
if ($user->email === false || $user->email == '') {
    $error .= 'Email inválido. ';
}

// Password
if ($user->password == '') {
    $error .= 'Password não definida. ';
} elseif (strlen($user->password) < 8) {
    $error .= 'Password deve ter no mínimo 8 caracteres. ';
}

// Nome
if ($user->full_name == '') {
    $error .= 'Nome não definido. ';
}

// Email já existe
if ($user->email && $user->emailExists()) {
    $error .= 'Email já registado. ';
}

if ($error !== "") {
    http_response_code(400);
    echo json_encode(["error" => "Erro no pedido: $error"]);
    exit();
}

// Criar utilizador
if ($user->create()) {
    http_response_code(201);
    echo json_encode(["message" => "Utilizador registado com sucesso"]);
} else {
    http_response_code(503);
    echo json_encode(["error" => "Erro ao criar utilizador"]);
}