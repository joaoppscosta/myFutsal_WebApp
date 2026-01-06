<?php

/**
 * Endpoint: juntar utilizador a uma equipa através de token
 * POST /api/team_join
 */
require_once './objects/TeamInvite.php';
require_once './objects/TeamMember.php';

/**
 * Apenas POST
 */
if ($method !== 'POST') {
    $code = 405;
    $response = ["error" => "Método não permitido"];
    return;
}

/**
 * Verificar autenticação (JWT)
 */
$user = api_get_authenticated_user();
if (!$user) {
    $code = 401;
    $response = ["error" => "Não autenticado"];
    return;
}

/**
 * Validar token recebido
 */
$token = $body->token ?? null;

if (!$token) {
    $code = 400;
    $response = ["error" => "Token em falta"];
    return;
}

/**
 * Procurar convite
 */
$invite = new TeamInvite($pdo);
$invite->token = $token;

$data = $invite->readByToken();

if (!$data) {
    $code = 404;
    $response = ["error" => "Convite inválido ou expirado"];
    return;
}

/**
 * Verificar se já pertence à equipa
 */
$sql = "
    SELECT member_id
    FROM team_members
    WHERE team_id = :TEAM
      AND user_id = :USER
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':TEAM', $data['team_id'], PDO::PARAM_INT);
$stmt->bindValue(':USER', $user['user_id'], PDO::PARAM_INT);
$stmt->execute();

if ($stmt->fetch()) {
    $code = 409;
    $response = ["error" => "Utilizador já pertence à equipa"];
    return;
}

/**
 * Adicionar utilizador à equipa
 */
$member = new TeamMember($pdo);
$member->team_id = $data['team_id'];
$member->user_id = $user['user_id'];
$member->profile = 'jogador';
$member->is_active = 1;

if (!$member->create()) {
    $code = 500;
    $response = ["error" => "Erro ao associar utilizador à equipa"];
    return;
}

/**
 * Marcar convite como usado
 */
$invite->invite_id = $data['invite_id'];
$invite->used_by = $user['user_id'];
$invite->markAsUsed();

/**
 * Sucesso
 */
$response = [
    "success" => true,
    "team_id" => $data['team_id'],
    "message" => "Utilizador associado à equipa com sucesso"
];
