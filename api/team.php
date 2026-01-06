<?php

require_once './objects/Team.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

$pdo = connectDB($db);
$team = new Team($pdo);

// Ler JWT do header
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    $code = 401;
    $response = ["error" => "Authorization header em falta"];
} else {
    $jwt = str_replace('Bearer ', '', $headers['Authorization']);

    try {
        $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);
        $user_id = (int) $decoded->data->user_id;

        // Métodos Suportados (Apenas Read e readOne porque CRUD da app mobile é apenas para consulta)
        switch ($method) {

            case 'GET':
                $code = 200;

                // READ ONE
                if (!is_null($id)) {
                    $team->team_id = $id;
                    $team->readOne();

                    if (!$team->team_name) {
                        $code = 404;
                        $response = ["error" => "Equipa não encontrada"];
                    } else {
                        $response = ["records" => [$team]];
                    }

                // READ (só equipas do utilizador)
                } else {
                    $stmt = $team->readByUser($user_id);
                    $response = ["records" => $stmt->fetchAll()];
                }
                break;

            default:
                $code = 405;
                $response = ["error" => "Método não suportado"];
        }

    } catch (Exception $e) {
        $code = 401;
        $response = ["error" => "Acesso negado: " . $e->getMessage()];
    }
}