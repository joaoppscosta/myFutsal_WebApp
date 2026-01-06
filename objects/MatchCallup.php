<?php

class MatchCallup {

    private $conn;
    private $table = "match_callups";
    // Campos da tabela
    public $callup_id;
    public $match_id;
    public $user_id;
    public $is_starter = 0;
    public $position;
    public $confirmation_status = 'pendente';
    public $confirmed_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /* =========================================
      Listar convocados de um jogo
      ========================================= */

    public function readByMatch() {

        $query = "
            SELECT 
                mc.callup_id,
                mc.user_id,
                mc.is_starter,
                mc.position,
                mc.confirmation_status,
                mc.confirmed_at,
                u.full_name,
                u.email
            FROM {$this->table} mc
            JOIN users u ON u.user_id = mc.user_id
            WHERE mc.match_id = :match_id
            ORDER BY mc.is_starter DESC, u.full_name
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':match_id', $this->match_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    /* =========================================
      Verificar se jogador já está convocado
      ========================================= */

    public function exists() {

        $query = "
            SELECT callup_id
            FROM {$this->table}
            WHERE match_id = :match_id
              AND user_id = :user_id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':match_id', $this->match_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* =========================================
      Criar convocatória (convocar jogador)
      ========================================= */

    public function create() {

        $query = "
            INSERT INTO {$this->table}
            (match_id, user_id, is_starter, position, confirmation_status)
            VALUES
            (:match_id, :user_id, :is_starter, :position, :confirmation_status)
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':match_id', $this->match_id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(':is_starter', $this->is_starter, PDO::PARAM_INT);
        $stmt->bindValue(':position', $this->position);
        $stmt->bindValue(':confirmation_status', $this->confirmation_status);

        return $stmt->execute();
    }

    /* =========================================
      Atualizar convocação (titular, posição, estado)
      ========================================= */

    public function update() {

        $query = "
            UPDATE {$this->table}
            SET
                is_starter = :is_starter,
                position = :position,
                confirmation_status = :confirmation_status,
                confirmed_at = :confirmed_at
            WHERE callup_id = :callup_id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':is_starter', $this->is_starter, PDO::PARAM_INT);
        $stmt->bindValue(':position', $this->position);
        $stmt->bindValue(':confirmation_status', $this->confirmation_status);
        $stmt->bindValue(':confirmed_at', $this->confirmed_at);
        $stmt->bindValue(':callup_id', $this->callup_id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /* =========================================
      Remover jogador da convocatória
      ========================================= */

    public function delete() {

        $query = "
            DELETE FROM {$this->table}
            WHERE callup_id = :callup_id
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':callup_id', $this->callup_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
