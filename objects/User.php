<?php

class User {

    // Ligação à BD e tabela
    private $conn;
    private $table_name = "users";
    // Propriedades
    public $user_id;
    public $email;
    public $password;
    public $profile;
    public $full_name;
    public $phone_number;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar utilizador
     */
    public function create() {

        $query = "INSERT INTO " . $this->table_name . "
            SET
                email = :email,
                password = :password,
                full_name = :full_name,
                phone_number = :phone_number,
                profile = :profile";

        $stmt = $this->conn->prepare($query);

        // Sanitização
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->full_name = trim($this->full_name);
        $this->phone_number = filter_var($this->phone_number, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->profile = filter_var($this->profile, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Hash da password
        $password_hash = password_hash($this->password, PASSWORD_ARGON2ID);

        // Bind
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':password', $password_hash);
        $stmt->bindValue(':full_name', $this->full_name);
        $stmt->bindValue(':phone_number', $this->phone_number);
        $stmt->bindValue(':profile', $this->profile);

        return $stmt->execute();
    }

    /**
     * Ler um user
     */
    public function readOne() {

        $query = "SELECT *
                  FROM " . $this->table_name . "
                  WHERE user_id = :ID
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $id = filter_var($this->user_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":ID", $id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $this->email = $row['email'];
        $this->password = $row['password'];
        $this->full_name = $row['full_name'];
        $this->profile = $row['profile'];
        $this->phone_number = $row['phone_number'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];

        return true;
    }

    /**
     * Atualizar user
     */
    public function update() {

        // Atualizar password só se for enviada
        $password_set = !empty($this->password) ? ", password = :password" : "";

        $query = "UPDATE " . $this->table_name . "
        SET
            email = :email,
            full_name = :full_name,
            phone_number = :phone_number
            {$password_set}
        WHERE user_id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitização correta (UTF-8 safe)
        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $this->full_name = trim($this->full_name);

        // phone_number pode ser NULL (questão de privacidade)
        $this->phone_number = !empty($this->phone_number) ? filter_var($this->phone_number, FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;

        // Binds principais
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':full_name', $this->full_name);
        $stmt->bindValue(':phone_number', $this->phone_number);
        $stmt->bindValue(':id', $this->user_id, PDO::PARAM_INT);

        // Password (se existir)
        if (!empty($this->password)) {
            $password_hash = password_hash($this->password, PASSWORD_ARGON2ID);
            $stmt->bindValue(':password', $password_hash);
        }

        return $stmt->execute();
    }

    /**
     * Verifica se email existe
     */
    public function emailExists() {

        $query = "SELECT *
                  FROM " . $this->table_name . "
                  WHERE email = ?
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL);
        $stmt->bindValue(1, $this->email);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Carregar propriedades
            $this->user_id = $row['user_id'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->full_name = $row['full_name'];
            $this->phone_number = $row['phone_number'];
            $this->profile = $row['profile'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            return true;
        }

        return false;
    }

    /**
     * Verificar password introduzida
     */
    public function verifyPassword($password_plain) {
        return password_verify($password_plain, $this->password);
    }

    /**
     * Ler todos os users
     */
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY full_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
