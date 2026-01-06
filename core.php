<?php

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

/**
 * Cria uma ligação a uma base de dados e devolve um objeto PDO com a ligação
 * @param Array $db
 * @return \PDO
 */
function connectDB($db) {
    try {
        $pdo = new PDO(
                'mysql:host=' . $db['host'] . '; ' . // string de ligação
                'port=' . $db['port'] . ';' . // string de ligação
                'charset=' . $db['charset'] . ';' . // string de ligação
                'dbname=' . $db['dbname'] . ';', // string de ligação
                $db['username'], // username
                $db['password'], // password
                [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
        );
    } catch (PDOException $e) {
        die('Erro ao ligar ao servidor ' . $e->getMessage());
    }
    // Definir array associativo como default para fetch()
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Definir lançamento de exceção para erros PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

/**
 * Verifica se o modo DEBUG está definido e ativo e escreve na consola do browser
 * @param mixed $info
 * @param sting $type [log, error, info]
 * @return bool
 */
function debug($info = '', $type = 'log') {
    if (defined('DEBUG') && DEBUG) {
        echo "<script>console.$type(" . json_encode($info, JSON_PRETTY_PRINT) . ");</script>";
        return true;
    }
    return false;
}

/**
 * Verifica se o modo DEBUG está definido e ativo e acrescenta $info ao array $_DEBUG
 * @global array $_DEBUG
 * @param array $info array("key"=>"value")
 * @return bool
 */
function debug_array($info = '') {
    if (defined('DEBUG') && DEBUG) {
        global $_DEBUG;
        $_DEBUG[] = $info;
        return true;
    }
    return false;
}

/**
 * Obtém módulo a partir do PATHINFO e valida
 * Devolve false caso não possua módulo ou o mesmo não seja válido
 * @return mixed
 */
function get_path_module() {
    $pathinfo = filter_input(INPUT_SERVER, 'PATH_INFO');
    if (empty($pathinfo)) {
        return false;
    }
    $patharray = explode("/", $pathinfo);
    if (in_array($patharray[1], API_MODULES)) {
        return $patharray[1];
    } else {
        return false;
    }
}

/**
 * Obtém id a partir do PATHINFO e valida
 * Devolve null caso não possua id ou id válido
 * @return mixed
 */
function get_path_id() {
    $pathinfo = filter_input(INPUT_SERVER, 'PATH_INFO');
    if (empty($pathinfo)) {
        return null;
    }
    $patharray = explode("/", $pathinfo);
    if (!empty($patharray) && isset($patharray[2]) && filter_var($patharray[2], FILTER_VALIDATE_INT)) {
        return (int) $patharray[2];
    } else {
        return null;
    }
}

/**
 * Verifica se o utilizador autenticado é admin
 * @return boolean
 */
function is_admin() {
    if (isset($_SESSION['email']) && $_SESSION['profile'] == 'admin') {
        return true;
    } else {
        return false;
    }
}

// Função para validar JWT
function api_get_authenticated_user() {

    global $jwt_conf;

    // Ler headers
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        return false;
    }

    // Espera: Authorization: Bearer <JWT>
    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
        return false;
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, $jwt_conf['key'], ['HS256']);

        if (empty($decoded->data->user_id)) {
            return false;
        }

        return [
            'user_id' => $decoded->data->user_id,
            'full_name' => $decoded->data->full_name ?? null,
            'email' => $decoded->data->email ?? null,
            'profile' => $decoded->data->profile ?? null,
            'phone_number' => $decoded->data->phone_number ?? null
        ];
    } catch (Exception $e) {
        return false;
    }
}

// Função para verificar se user pertence à equipa em questão
function user_belongs_to_team(PDO $pdo, int $user_id, int $team_id): bool {
    $sql = "
        SELECT 1
        FROM team_members
        WHERE user_id = :user_id
          AND team_id = :team_id
          AND is_active = 1
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':team_id', $team_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->rowCount() > 0;
}

/**
 * Envia uma resposta JSON estruturada
 */
function json_response(int $code, array $response) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code($code);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
