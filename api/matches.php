<?php

require_once './objects/TeamMatch.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

$pdo = connectDB($db);
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

    // READ ONE
    if (!is_null($id)) {
        $match->match_id = $id;
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

        $response = ["record" => $match];
        $code = 200;

    // READ (por equipa)
    } else {
        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);

        if (!$team_id || !user_belongs_to_team($pdo, $user_id, $team_id)) {
            $code = 403;
            $response = ["error" => "Acesso negado"];
            return;
        }

        $stmt = $match->readByTeam($team_id);
        $response = ["records" => $stmt->fetchAll()];
        $code = 200;
    }

} catch (Exception $e) {
    $code = 401;
    $response = ["error" => "Token inválido"];
}