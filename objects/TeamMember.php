<?php

class TeamMember {

    // Ligação à Base de Dados e nome da tabela
    private $conn;
    private $table_name = "team_members";
    // Propriedades (colunas)
    public $member_id;
    public $team_id;
    public $user_id;
    public $profile;
    public $join_date;
    public $is_active;
    public $full_name; // do utilizador (JOIN)

    /**
     * Construtor com a ligação PDO
     */
    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    /**
     * READ — Ler todos os membros de uma equipa
     */
    public function read($team_id) {

        $query = "SELECT 
                    tm.member_id,
                    tm.team_id,
                    tm.user_id,
                    tm.profile,
                    tm.join_date,
                    tm.is_active,
                    u.full_name
                  FROM " . $this->table_name . " tm
                  INNER JOIN users u ON tm.user_id = u.user_id
                  WHERE tm.team_id = :team_id
                  ORDER BY u.full_name ASC";

        $stmt = $this->conn->prepare($query);

        $team_id = filter_var($team_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":team_id", $team_id);

        $stmt->execute();
        return $stmt;
    }

    /**
     * READ ONE — Obter um membro pelo member_id
     */
    public function readOne() {

        $query = "SELECT 
                    tm.member_id,
                    tm.team_id,
                    tm.user_id,
                    tm.profile,
                    tm.join_date,
                    tm.is_active,
                    u.full_name
                  FROM " . $this->table_name . " tm
                  INNER JOIN users u ON tm.user_id = u.user_id
                  WHERE tm.member_id = :member_id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->member_id = filter_var($this->member_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(':member_id', $this->member_id);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->team_id = $row['team_id'];
            $this->user_id = $row['user_id'];
            $this->profile = $row['profile'];
            $this->join_date = $row['join_date'];
            $this->is_active = $row['is_active'];
            $this->full_name = $row['full_name'];
        }
    }

    /**
     * CREATE — Adicionar membro à equipa
     */
    public function create() {

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                    team_id = :team_id,
                    user_id = :user_id,
                    profile = :profile,
                    is_active = :is_active,
                    join_date = :join_date";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitização
            $this->team_id = filter_var($this->team_id, FILTER_SANITIZE_NUMBER_INT);
            $this->user_id = filter_var($this->user_id, FILTER_SANITIZE_NUMBER_INT);
            $this->profile = filter_var($this->profile, FILTER_UNSAFE_RAW);
            $this->is_active = filter_var($this->is_active, FILTER_SANITIZE_NUMBER_INT);

            // Bind
            $stmt->bindValue(":team_id", $this->team_id);
            $stmt->bindValue(":user_id", $this->user_id);
            $stmt->bindValue(":profile", $this->profile);
            $stmt->bindValue(":is_active", $this->is_active);
            $stmt->bindValue(":join_date", date('Y-m-d H:i:s'));

            return $stmt->execute();
        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    /**
     * UPDATE — Atualizar perfil ou estado do membro
     */
    public function update() {

        $query = "UPDATE " . $this->table_name . "
                  SET
                    profile = :profile,
                    is_active = :is_active
                  WHERE
                    member_id = :member_id";

        try {
            $stmt = $this->conn->prepare($query);

            // Sanitização
            $this->profile = filter_var($this->profile, FILTER_UNSAFE_RAW);
            $this->is_active = filter_var($this->is_active, FILTER_SANITIZE_NUMBER_INT);
            $this->member_id = filter_var($this->member_id, FILTER_SANITIZE_NUMBER_INT);

            // Bind
            $stmt->bindValue(":profile", $this->profile);
            $stmt->bindValue(":is_active", $this->is_active);
            $stmt->bindValue(":member_id", $this->member_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            debug("PDOException: " . $e->getMessage());
        }

        return false;
    }

    /**
     * DELETE — Remover membro da equipa
     */
    public function delete() {

        $query = "DELETE FROM " . $this->table_name . "
                  WHERE member_id = :member_id";

        $stmt = $this->conn->prepare($query);

        $this->member_id = filter_var($this->member_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindValue(":member_id", $this->member_id);

        return $stmt->execute();
    }
    
    /**
     * countAdminsInTeam — Função para contar os admins para não poder remover se for o último admin da equipa
     */
    public function countAdminsInTeam($team_id) {
        $query = "SELECT COUNT(*) AS total 
              FROM team_members 
              WHERE team_id = :team_id AND profile = 'admin' AND is_active = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':team_id', $team_id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? intval($row['total']) : 0;
    }
    
    /**
     * getUserProfileInTeam — Função para ir buscar o "profile" do utilizador na equipa em questão
     */
    public function getUserProfileInTeam($user_id, $team_id) {
        $query = "SELECT profile 
              FROM team_members
              WHERE user_id = :uid AND team_id = :tid LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":uid", $user_id);
        $stmt->bindValue(":tid", $team_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['profile'] : null;
    }
}
