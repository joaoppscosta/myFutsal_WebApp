<?php

class Training {

    private $conn;
    private $table_name = "trainings";

    // Colunas da tabela
    public $training_id;
    public $team_id;
    public $training_date;
    public $start_time;
    public $end_time;
    public $location;
    public $description;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;

    // Join extras
    public $creator_name;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // Ler todos os treinos de uma equipa
    public function readByTeam($team_id) {
        $query = "SELECT t.*, u.full_name AS creator_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.created_by = u.user_id
                  WHERE t.team_id = :team_id
                  ORDER BY t.training_date DESC, t.start_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":team_id", $team_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Criar treino
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    team_id = :team_id,
                    training_date = :training_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    location = :location,
                    description = :description,
                    created_by = :created_by,
                    created_at = :created_at";

        try {
            $stmt = $this->conn->prepare($query);

            $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);
            $this->training_date = filter_var($this->training_date, FILTER_UNSAFE_RAW);
            $this->start_time = filter_var($this->start_time, FILTER_UNSAFE_RAW);
            $this->end_time = filter_var($this->end_time, FILTER_UNSAFE_RAW);
            $this->location = filter_var($this->location, FILTER_UNSAFE_RAW);
            $this->description = filter_var($this->description, FILTER_UNSAFE_RAW);
            $this->created_by = filter_var($this->created_by, FILTER_SANITIZE_NUMBER_INT);

            $stmt->bindValue(":team_id", $this->team_id, PDO::PARAM_INT);
            $stmt->bindValue(":training_date", $this->training_date);
            $stmt->bindValue(":start_time", $this->start_time);
            $stmt->bindValue(":end_time", $this->end_time);
            $stmt->bindValue(":location", $this->location);
            $stmt->bindValue(":description", $this->description);
            $stmt->bindValue(":created_by", $this->created_by, PDO::PARAM_INT);
            $stmt->bindValue(":created_at", date('Y-m-d H:i:s'));

            return $stmt->execute();

        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    // Ler um treino por id
    public function readOne() {
        $query = "SELECT t.*, u.full_name AS creator_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.created_by = u.user_id
                  WHERE t.training_id = :training_id
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);

        $this->training_id = filter_var($this->training_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(':training_id', $this->training_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->team_id = $row['team_id'];
            $this->training_date = $row['training_date'];
            $this->start_time = $row['start_time'];
            $this->end_time = $row['end_time'];
            $this->location = $row['location'];
            $this->description = $row['description'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->updated_by = $row['updated_by'] ?? null;
            $this->updated_at = $row['updated_at'] ?? null;
            $this->creator_name = $row['creator_name'] ?? null;
        }
    }

    // Atualizar treino
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                    training_date = :training_date,
                    start_time = :start_time,
                    end_time = :end_time,
                    location = :location,
                    description = :description,
                    updated_by = :updated_by,
                    updated_at = :updated_at
                  WHERE training_id = :training_id";
        try {
            $stmt = $this->conn->prepare($query);

            $this->training_id = filter_var($this->training_id, FILTER_SANITIZE_NUMBER_INT);
            $this->training_date = filter_var($this->training_date, FILTER_UNSAFE_RAW);
            $this->start_time = filter_var($this->start_time, FILTER_UNSAFE_RAW);
            $this->end_time = filter_var($this->end_time, FILTER_UNSAFE_RAW);
            $this->location = filter_var($this->location, FILTER_UNSAFE_RAW);
            $this->description = filter_var($this->description, FILTER_UNSAFE_RAW);
            $this->updated_by = filter_var($this->updated_by, FILTER_SANITIZE_NUMBER_INT);

            $stmt->bindValue(":training_date", $this->training_date);
            $stmt->bindValue(":start_time", $this->start_time);
            $stmt->bindValue(":end_time", $this->end_time);
            $stmt->bindValue(":location", $this->location);
            $stmt->bindValue(":description", $this->description);
            $stmt->bindValue(":updated_by", $this->updated_by, PDO::PARAM_INT);
            $stmt->bindValue(":updated_at", date('Y-m-d H:i:s'));
            $stmt->bindValue(":training_id", $this->training_id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }
        return false;
    }

    // Apagar treino
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE training_id = :training_id";
        $stmt = $this->conn->prepare($query);

        $this->training_id = filter_var($this->training_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":training_id", $this->training_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}