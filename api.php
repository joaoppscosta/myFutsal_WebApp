<?php

require_once './config.php';
require_once './core.php';

// Cabeçalho da resposta
header("Content-Type: application/json; charset=UTF-8");

$pdo = connectDB($db);

// Obter Módulo
$module = get_path_module();
$id = get_path_id();

debug_array(["MODULE" => $module]);
debug_array(["ID" => $id]);

if (!$module || !file_exists("./api/$module.php")) {
    json_response(404, ["error" => "Endpoint desconhecido"]);
}

/* Ler o corpo do pedido */
$body = json_decode(file_get_contents("php://input"));
$method = $body->method ?? "GET";

debug_array(["METHOD" => $method]);

$response = [];
$code = 200;

// Executar módulo
require "./api/$module.php";

// Acrescentar debug
if (DEBUG && isset($_DEBUG)) {
    $response["debug"] = $_DEBUG;
}

json_response($code, $response);