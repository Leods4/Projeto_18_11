<?php
require 'conexao.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se todos os campos foram preenchidos
    if (isset($_POST['email'], $_POST['nome'], $_POST['nova_senha'])) {
        $email = $_POST['email'];
        $nome = $_POST['nome'];
        $nova_senha = $_POST['nova_senha'];

        // Verifica se o e-mail e nome correspondem no banco
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND LOWER(nome) = LOWER(?)");
        $stmt->bind_param("ss", $email, $nome);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($usuario_id);
            $stmt->fetch();

            // Atualiza a senha no banco de dados
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $update_stmt->bind_param("si", $senha_hash, $usuario_id);

            if ($update_stmt->execute()) {
                $success = "Senha redefinida com sucesso!";
                header("Location: login.php"); // Redireciona para a página de login
                exit;
            } else {
                $error = "Erro ao redefinir a senha. Tente novamente.";
            }

            $update_stmt->close();
        } else {
            $error = "Email ou nome inválidos.";
        }
        $stmt->close();
    } else {
        $error = "Preencha todos os campos.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
<header>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="registro.php">Criar Conta</a></li>
            </ul>
        </nav>
    </header>
    <h2>Redefinir Senha</h2>
    <!-- Exibe mensagem de erro, se existir -->
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <!-- Formulário único para redefinição -->
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="nome" placeholder="Nome Completo" required>
        <input type="password" name="nova_senha" placeholder="Nova Senha" required>
        <button type="submit">Redefinir Senha</button>
    </form>
</body>
</html>
