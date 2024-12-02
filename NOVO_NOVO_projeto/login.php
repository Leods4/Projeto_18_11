<?php
session_start(); // Inicia a sessão
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['tentativas'])) {
        $_SESSION['tentativas'] = 0;
    }

    if (!isset($_SESSION['bloqueio'])) {
        $_SESSION['bloqueio'] = false;
    }

    // Verifica se o login está bloqueado
    if ($_SESSION['bloqueio']) {
        $diferenca_tempo = time() - $_SESSION['ultimo_tempo_bloqueio'];
        if ($diferenca_tempo >= 10) {
            // Desbloqueia após 10 segundos
            $_SESSION['bloqueio'] = false;
            $_SESSION['tentativas'] = 0;
        } else {
            $tempo_restante = 10 - $diferenca_tempo;
            $error = "Login bloqueado. Tente novamente em $tempo_restante segundos.";
        }
    }

    if (!$_SESSION['bloqueio']) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $stmt = $conn->prepare("SELECT id, senha, tipo FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($usuario_id, $senha_hash, $tipo);
            $stmt->fetch();

            if (password_verify($senha, $senha_hash)) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario_id;
                $_SESSION['tipo_usuario'] = $tipo;
                $_SESSION['tentativas'] = 0; // Reseta as tentativas em caso de sucesso
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['tentativas']++;
                if ($_SESSION['tentativas'] >= 3) {
                    $_SESSION['bloqueio'] = true;
                    $_SESSION['ultimo_tempo_bloqueio'] = time();
                    $error = "Muitas tentativas falhas. Login bloqueado por 10 segundos.";
                } else {
                    $error = "Senha incorreta.";
                }
            }
        } else {
            $_SESSION['tentativas']++;
            if ($_SESSION['tentativas'] >= 3) {
                $_SESSION['bloqueio'] = true;
                $_SESSION['ultimo_tempo_bloqueio'] = time();
                $error = "Muitas tentativas falhas. Login bloqueado por 10 segundos.";
            } else {
                $error = "Email não encontrado.";
            }
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Estacionamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
            <img src="icone.png" alt="Logo" class="header-logo">
                <li><a href="index.php">Início</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="registro.php">Criar Conta</a></li>
            </ul>
        </nav>
    </header>


    <main>
        <section class="login">
            <h2>Login</h2>
            <form action="" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Entrar</button>
            </form>
            <p class="error-message" style="color: red;">
                <?php if (isset($error)) echo htmlspecialchars($error); ?>
            </p>
            <p><a href="redefinir_senha.php">Esqueceu a senha?</a></p>
        </section>
    </main>

</body>
</html>