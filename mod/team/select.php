<?php
if (count(get_included_files()) == 1) {
    exit("Direct access not permitted.");
}

$team_id = filter_input(INPUT_GET, 'team_id', FILTER_SANITIZE_NUMBER_INT);

if (!$team_id) {
    echo '<div class="alert alert-danger">Equipa inválida.</div>';
    exit();
}

// Validar que o utilizador pertence a esta equipa
$user_id = $_SESSION['user_id'];

$sql = "SELECT profile 
        FROM team_members 
        WHERE team_id = :TEAM AND user_id = :USER 
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':TEAM', $team_id);
$stmt->bindValue(':USER', $user_id);
$stmt->execute();

if ($stmt->rowCount() != 1) {
    echo '<div class="alert alert-danger">Não pertence a esta equipa.</div>';
    exit();
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Guardar na sessão
$_SESSION['current_team'] = $team_id;
$_SESSION['current_team_profile'] = $row['profile'];

// Redirecionar para dashboard da equipa
header("Location: index.php?m=team&a=dashboard");
exit();