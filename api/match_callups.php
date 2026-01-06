<?php

require_once './objects/MatchCallup.php';
require_once './objects/TeamMatch.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

$pdo = connectDB($db);
$callup = new MatchCallup($pdo);
$match = new TeamMatch($pdo);

$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    $code = 401;
    $response = ["error" => "Authorization header em falta"];
    return;
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);

try {
    $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);
    $user_id = (int) $decoded->data->user_id;

    if ($method !== 'GET') {
        $code = 405;
        $response = ["error" => "Método não suportado"];
        return;
    }

    $match_id = filter_input(INPUT_GET, 'match_id', FILTER_VALIDATE_INT);

    if (!$match_id) {
        $code = 400;
        $response = ["error" => "match_id em falta"];
        return;
    }

    // Validar jogo
    $match->match_id = $match_id;
    $match->readOne();

    if (!$match->team_id) {
        $code = 404;
        $response = ["error" => "Jogo não encontrado"];
        return;
    }

    if (!user_belongs_to_team($pdo, $user_id, $match->team_id)) {
        $code = 403;
        $response = ["error" => "Acesso negado"];
        return;
    }

    // Ler convocatória
    $callup->match_id = $match_id;
    $stmt = $callup->readByMatch();

    $response = ["records" => $stmt->fetchAll()];
    $code = 200;

} catch (Exception $e) {
    $code = 401;
    $response = ["error" => "Token inválido"];
}