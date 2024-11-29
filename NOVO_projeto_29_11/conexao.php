<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    session_regenerate_id(true);
}

$host = "localhost";
$usuario = 'root';
$senha = "";     
$database = "projeto";

$conn = new mysqli($host, $usuario, $senha, $database);

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Falha na conexão. Por favor, tente novamente mais tarde.");
}

function logout() {
    if (isset($_SESSION['usuario_id'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
    } else {
        header('Location: index.php?error=no_session');
    }
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}
?>
