<?php

// Configurações base
require_once '../../config.php';
require_once '../../core.php';
$pdo = connectDB($db);

// Classe User
require_once '../../objects/User.php';
$user = new User($pdo);

// JWT
require '../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Cabeçalho
header("Content-Type: application/json; charset=UTF-8");

// Ler body
$data = json_decode(file_get_contents("php://input"));

// JWT
$jwt = $data->jwt ?? '';

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["error" => "Acesso Negado"]);
    exit();
}

try {
    // Validar token
    $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);

    // ID vem do token
    $user->user_id = filter_var($decoded->data->user_id, FILTER_SANITIZE_NUMBER_INT);

    // Carregar dados atuais do user (incluindo profile)
    $user->readOne();

    // Validar dados
    $user->email = isset($data->email) ? filter_var($data->email, FILTER_SANITIZE_EMAIL) : '';

    if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Email inválido"]);
        exit();
    }

    $user->full_name = isset($data->full_name) ? trim($data->full_name) : '';

    $user->phone_number = isset($data->phone_number) ? filter_var($data->phone_number, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

    // Alterar Password
    if (isset($data->password) && trim($data->password) !== '') {
        if (strlen($data->password) < 8) {
            http_response_code(400);
            echo json_encode(["error" => "Password deve ter pelo menos 8 caracteres"]);
            exit();
        }
        $user->password = $data->password;
    } else {
        // IMPORTANTE: impedir update da password
        $user->password = null;
    }

    if ($user->update()) {
        // Gerar novo token
        $token = array(
            "iss" => $jwt_conf['iss'],
            "jti" => $jwt_conf['jti'],
            "iat" => $jwt_conf['iat'],
            "nbf" => $jwt_conf['nbf'],
            "exp" => $jwt_conf['exp'],
            "data" => array(
                "user_id" => $user->user_id,
                "full_name" => $user->full_name,
                "email" => $user->email,
                "phone_number" => $user->phone_number,
                "profile" => $user->profile
            )
        );
        // Criar novo token
        $newJwt = JWT::encode($token, $jwt_conf['key']);

        // Sucesso na operação - 200 OK
        $code = 200;
        $response = ["message" => "Atualizado com sucesso", "jwt" => $newJwt];
    } else {
        // Erro ao atualizar - 503 service unavailable
        $code = 503;
        $response = ["error" => "Erro ao atualizar registo"];
    }
} catch (Exception $e) {
    // Acesso negado - 401 Unauthorized
    $code = 401;
    $response = ["error" => "Erro no pedido: " . $e->getMessage()];
}

// Cabeçalho da resposta
header("Content-Type: application/json; charset=UTF-8");
// Código de resposta
http_response_code($code);
// Corpo da resposta
echo json_encode($response);
