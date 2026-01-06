<?php
require_once './config.php';
require_once './core.php';
// Iniciar sessão
session_start();

// Destruir sessão
session_destroy();

// Efetuar redirecionamento
header('Location: login.php');
exit();
