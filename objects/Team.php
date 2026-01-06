<?php

class Team {

    // Ligação à Base de Dados e nome da tabela
    private $conn;
    private $table_name = "teams";
    // Propriedades (colunas)
    public $team_id;
    public $team_name;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $logo_url;
    public $description;

    /**
     * Construtor com a ligação PDO
     */
    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    /**
     * READ — Ler todas as equipas
     */
    public function read() {

        $query = "SELECT 
                t.team_id, t.team_name, t.logo_url, t.description,
                t.created_by, t.created_at,
                t.updated_by, t.updated_at,
                u.full_name AS creator_name,
                u2.full_name AS updater_name
              FROM " . $this->table_name . " t
              LEFT JOIN users u ON t.created_by = u.user_id
              LEFT JOIN users u2 ON t.updated_by = u2.user_id
              ORDER BY t.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * READBYUSER — Ler todas as equipas em que o utilizador seja um team_member dessas mesmas equipas
     */
    public function readByUser($user_id) {

        $query = "SELECT 
                t.team_id, t.team_name, t.logo_url, t.description,
                t.created_by, t.created_at,
                t.updated_by, t.updated_at,
                u.full_name AS creator_name,
                u2.full_name AS updater_name
              FROM teams t
              INNER JOIN team_members tm ON tm.team_id = t.team_id
              LEFT JOIN users u ON t.created_by = u.user_id
              LEFT JOIN users u2 ON t.updated_by = u2.user_id
              WHERE tm.user_id = :UID
              ORDER BY t.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":UID", $user_id);
        $stmt->execute();
        return $stmt;
    }

    /**
     * CREATE — Criar nova equipa
     */
    public function create() {

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    team_name = :team_name,
                    description = :description,
                    logo_url = :logo_url,
                    created_by = :created_by,
                    created_at = :created_at";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitização
            $this->team_name = filter_var($this->team_name, FILTER_UNSAFE_RAW);
            $this->description = filter_var($this->description, FILTER_UNSAFE_RAW);
            $this->logo_url = filter_var($this->logo_url, FILTER_UNSAFE_RAW);
            $this->created_by = filter_var($this->created_by, FILTER_SANITIZE_NUMBER_INT);

            // Afetar Valores
            $stmt->bindValue(":team_name", $this->team_name);
            $stmt->bindValue(":description", $this->description);
            $stmt->bindValue(":logo_url", $this->logo_url);
            $stmt->bindValue(":created_by", $this->created_by);
            $stmt->bindValue(":created_at", date('Y-m-d H:i:s'));

            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            debug("PDOException: " . $e->getCode() . " - " . $e->getMessage());
        }

        return false;
    }

    /**
     * READ ONE — Obter uma equipa por ID
     */
    public function readOne() {

        $query = "SELECT 
                    t.team_id, t.team_name, t.logo_url, t.description,
                    t.created_by, t.created_at, t.updated_by, t.updated_at,
                    u.full_name as creator_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.created_by = u.user_id
                  WHERE
                    t.team_id = :team_id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(':team_id', $this->team_id);

        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->team_name = $row['team_name'];
            $this->logo_url = $row['logo_url'];
            $this->description = $row['description'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_by = $row['updated_by'];
            $this->updated_at = $row['updated_at'];
        }
    }

    /**
     * UPDATE — Atualizar equipa
     */
    public function update() {

        $query = "UPDATE " . $this->table_name . "
                  SET
                    team_name = :team_name,
                    description = :description,
                    logo_url = :logo_url,
                    updated_by = :updated_by,
                    updated_at = :updated_at
                  WHERE
                    team_id = :team_id";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitização
            $this->team_name = filter_var($this->team_name, FILTER_UNSAFE_RAW);
            $this->description = filter_var($this->description, FILTER_UNSAFE_RAW);
            $this->logo_url = filter_var($this->logo_url, FILTER_UNSAFE_RAW);
            $this->updated_by = filter_var($this->updated_by, FILTER_SANITIZE_NUMBER_INT);
            $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);

            // Bind
            $stmt->bindValue(":team_name", $this->team_name);
            $stmt->bindValue(":description", $this->description);
            $stmt->bindValue(":logo_url", $this->logo_url);
            $stmt->bindValue(":updated_by", $this->updated_by);
            $stmt->bindValue(":updated_at", date('Y-m-d H:i:s'));
            $stmt->bindValue(":team_id", $this->team_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    /**
     * DELETE — Apagar equipa
     */
    public function delete() {

        $query = "DELETE FROM " . $this->table_name . " WHERE team_id = :team_id";

        $stmt = $this->conn->prepare($query);

        $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":team_id", $this->team_id);

        return $stmt->execute();
    }
}
