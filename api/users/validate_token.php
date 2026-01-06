<?php

require_once '../../config.php';
require_once '../../core.php';

require '../../vendor/autoload.php';
use Firebase\JWT\JWT;

header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));
$jwt = $data->jwt ?? '';

if ($jwt) {
    try {
        // Decode do JWT
        $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);
        // Sucesso na operação - 200 OK
        $code = 200;
        $response = array(
            "message" => "Acesso autorizado",
            "data" => $decoded->data,
            "expiration" => date("c", $decoded->exp),
            "timeleft" => $decoded->exp-time(),
        );

    } catch (Exception $e) {
        // Acesso negado - 401 Unauthorized
        $code = 401;
        $response = ["error" => "Acesso negado. Erro: ".$e->getMessage()];
    }
} else {
    $code = 401;
    $response = ["error" => "Acesso negado."];
}

// Cabeçalho da resposta
header("Content-Type: application/json; charset=UTF-8");
// Código de resposta
http_response_code($code);
// Corpo da resposta
echo json_encode($response);