<?php

class TeamMatch {

    private $conn;
    private $table_name = "matches";

    public $match_id;
    public $team_id;
    public $opponent_name;
    public $match_date;
    public $match_time;
    public $location;
    public $match_type;
    public $is_home;
    public $result;
    public $team_goals;
    public $opponent_goals;
    public $created_by;
    public $created_at;

    public $creator_name;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    /** READ — jogos da equipa */
    public function readByTeam($team_id) {

        $query = "SELECT 
                    m.*, 
                    u.full_name AS creator_name
                  FROM " . $this->table_name . " m
                  LEFT JOIN users u ON m.created_by = u.user_id
                  WHERE m.team_id = :team_id
                  ORDER BY m.match_date DESC, m.match_time DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":team_id", $team_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /** CREATE — Criar novo jogo */
    public function create() {
        
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    team_id = :team_id,
                    opponent_name = :opponent_name,
                    match_date = :match_date,
                    match_time = :match_time,
                    location = :location,
                    match_type = :match_type,
                    is_home = :is_home,
                    result = 'pendente',
                    team_goals = 0,
                    opponent_goals = 0,
                    created_by = :created_by,
                    created_at = :created_at";
                    // Os team_goals e opponent_goals são definidos a 0 no create para não dar erro na BD
        try {
            $stmt = $this->conn->prepare($query);

            // Sanitização
            $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);
            $this->opponent_name = filter_var($this->opponent_name, FILTER_UNSAFE_RAW);
            $this->match_date = filter_var($this->match_date, FILTER_UNSAFE_RAW);
            $this->match_time = filter_var($this->match_time, FILTER_UNSAFE_RAW);
            $this->location = filter_var($this->location, FILTER_UNSAFE_RAW);
            $this->match_type = filter_var($this->match_type, FILTER_UNSAFE_RAW);
            $this->is_home = filter_var($this->is_home, FILTER_SANITIZE_NUMBER_INT);

            // Bind
            $stmt->bindValue(":team_id", $this->team_id);
            $stmt->bindValue(":opponent_name", $this->opponent_name);
            $stmt->bindValue(":match_date", $this->match_date);
            $stmt->bindValue(":match_time", $this->match_time);
            $stmt->bindValue(":location", $this->location);
            $stmt->bindValue(":match_type", $this->match_type);
            $stmt->bindValue(":is_home", $this->is_home);
            $stmt->bindValue(":created_by", $this->created_by);
            $stmt->bindValue(":created_at", date('Y-m-d H:i:s'));

            return $stmt->execute();

        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    /** READ ONE - jogo por ID */
    public function readOne() {

        $query = "SELECT 
                    m.*, 
                    u.full_name AS creator_name
                  FROM " . $this->table_name . " m
                  LEFT JOIN users u ON m.created_by = u.user_id
                  WHERE m.match_id = :match_id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->match_id = filter_var($this->match_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(':match_id', $this->match_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->team_id = $row['team_id'];
            $this->opponent_name = $row['opponent_name'];
            $this->match_date = $row['match_date'];
            $this->match_time = $row['match_time'];
            $this->location = $row['location'];
            $this->match_type = $row['match_type'];
            $this->is_home = $row['is_home'];
            $this->result = $row['result'];
            $this->team_goals = $row['team_goals'];
            $this->opponent_goals = $row['opponent_goals'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            $this->creator_name = $row['creator_name'];
        }
    }

    /** UPDATE — Atualizar jogo */
    public function update() {

        $query = "UPDATE " . $this->table_name . "
                  SET
                    opponent_name = :opponent_name,
                    match_date = :match_date,
                    match_time = :match_time,
                    location = :location,
                    match_type = :match_type,
                    is_home = :is_home,
                    result = :result,
                    team_goals = :team_goals,
                    opponent_goals = :opponent_goals
                  WHERE match_id = :match_id";

        try {
            $stmt = $this->conn->prepare($query);

            $this->match_id = filter_var($this->match_id, FILTER_SANITIZE_NUMBER_INT);

            $stmt->bindValue(":opponent_name", $this->opponent_name);
            $stmt->bindValue(":match_date", $this->match_date);
            $stmt->bindValue(":match_time", $this->match_time);
            $stmt->bindValue(":location", $this->location);
            $stmt->bindValue(":match_type", $this->match_type);
            $stmt->bindValue(":is_home", $this->is_home);
            $stmt->bindValue(":result", $this->result);
            $stmt->bindValue(":team_goals", $this->team_goals);
            $stmt->bindValue(":opponent_goals", $this->opponent_goals);
            $stmt->bindValue(":match_id", $this->match_id);

            return $stmt->execute();

        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    /** DELETE */
    public function delete() {

        $query = "DELETE FROM " . $this->table_name . " WHERE match_id = :match_id";

        $stmt = $this->conn->prepare($query);

        $this->match_id = filter_var($this->match_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":match_id", $this->match_id);

        return $stmt->execute();
    }
}