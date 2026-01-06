<?php

require_once '../../config.php';
require_once '../../core.php';
require_once '../../objects/User.php';

require '../../vendor/autoload.php';

use Firebase\JWT\JWT;

$pdo = connectDB($db);
$user = new User($pdo);

header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"));

if (!empty($data)) {

    $user->email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
    $password = $data->password ?? '';

    $error = '';

    if ($user->email == '') {
        $error .= 'O campo email não pode estar vazio. ';
    }
    if ($password == '') {
        $error .= 'O campo password não pode estar vazio. ';
    }
    if (!$user->emailExists()) {
        $error .= 'Credenciais inválidas. '; // Para evitar saberem se um email existe ou não
    }

    if ($error == '') {

        if (password_verify($password, $user->password)) {

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

            $jwt = JWT::encode($token, $jwt_conf['key'], 'HS256');

            $code = 200;
            $response = [
                "message" => "Autenticado com sucesso",
                "jwt" => $jwt
            ];
        } else {
            $code = 401;
            $response = ["error" => "Credenciais inválidas. "];
        }
    } else {
        $code = 400;
        $response = ["error" => $error];
    }
} else {
    $code = 400;
    $response = ["error" => "Pedido sem dados"];
}

http_response_code($code);
echo json_encode($response);
