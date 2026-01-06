<?php

require_once './objects/Training.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;

$pdo = connectDB($db);
$training = new Training($pdo);

// Ler JWT do header
$headers = getallheaders();

if (!isset($headers['Authorization'])) {
    $code = 401;
    $response = ["error" => "Authorization header em falta"];
    return;
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);

try {
    // Validar JWT
    $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);
    $user_id = (int) $decoded->data->user_id;

    // Apenas GET (mobile é só leitura)
    if ($method !== 'GET') {
        $code = 405;
        $response = ["error" => "Método não suportado"];
        return;
    }

    /* ===============================
       READ ONE - detalhe de um treino
    =============================== */
    if (!is_null($id)) {

        $training->training_id = $id;
        $training->readOne();

        if (!$training->team_id) {
            $code = 404;
            $response = ["error" => "Treino não encontrado"];
            return;
        }

        // Segurança: verificar se pertence à equipa
        if (!user_belongs_to_team($pdo, $user_id, $training->team_id)) {
            $code = 403;
            $response = ["error" => "Acesso negado"];
            return;
        }

        $response = ["record" => $training];
        $code = 200;

    /* ===============================
       READ - treinos de uma equipa
    =============================== */
    } else {

        $team_id = filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT);

        if (!$team_id || !user_belongs_to_team($pdo, $user_id, $team_id)) {
            $code = 403;
            $response = ["error" => "Acesso negado"];
            return;
        }

        $stmt = $training->readByTeam($team_id);
        $response = ["records" => $stmt->fetchAll()];
        $code = 200;
    }

} catch (Exception $e) {
    $code = 401;
    $response = ["error" => "Token inválido"];
}