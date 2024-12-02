<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    $liked = isset($_COOKIE['liked']) && $_COOKIE['liked'] == 'true';

    // Alterna o estado do cookie
    if ($liked) {
        setcookie('liked', 'false', time() - 3600, '/'); // Remove o cookie
    } else {
        setcookie('liked', 'true', time() + (86400 * 30), '/'); // Define o cookie por 30 dias
    }

    // Retorna uma resposta simples
    echo json_encode(['status' => 'success', 'liked' => !$liked]);
}