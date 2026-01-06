<?php

class TeamInvite {

    private $conn;
    private $table = "team_invites";
    public $invite_id;
    public $team_id;
    public $token;
    public $created_by;
    public $expires_at;
    public $used_by;
    public $used_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /* ===============================
      Criar convite
      =============================== */

    public function create() {

        $query = "
            INSERT INTO {$this->table}
            (team_id, token, created_by, expires_at)
            VALUES
            (:team_id, :token, :created_by, :expires_at)
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':team_id', $this->team_id, PDO::PARAM_INT);
        $stmt->bindValue(':token', $this->token);
        $stmt->bindValue(':created_by', $this->created_by, PDO::PARAM_INT);
        $stmt->bindValue(':expires_at', $this->expires_at);

        return $stmt->execute();
    }

    /* ===============================
      Apagar convite
      =============================== */

    public function delete() {

        $query = "
        DELETE FROM {$this->table}
        WHERE invite_id = :invite_id
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':invite_id', $this->invite_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /* ===============================
      Validar convite
      =============================== */

    public function readByToken() {

        $query = "
            SELECT *
            FROM {$this->table}
            WHERE token = :token
              AND used_at IS NULL
              AND (expires_at IS NULL OR expires_at > NOW())
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':token', $this->token);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* ===============================
      Marcar convite como usado
      =============================== */

    public function markAsUsed() {

        $query = "
            UPDATE {$this->table}
            SET used_by = :used_by,
                used_at = NOW()
            WHERE invite_id = :invite_id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':used_by', $this->used_by, PDO::PARAM_INT);
        $stmt->bindValue(':invite_id', $this->invite_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
