<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $tipo = preg_match('/@admin\.com$/', $email) ? 'administrador' : 'comum';
    } else {
        $error = "Email inválido para cadastro.";
    }

    if (!isset($error)) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Este email já está cadastrado.";
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $email, $senha, $tipo);

            if ($stmt->execute()) {
                $success = "Conta criada com sucesso. Faça login!";
            } else {
                $error = "Erro ao criar conta.";
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
    <title>Criar Conta - Sistema de Estacionamento</title>
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
        <section class="registro">
            <h2>Criar Conta</h2>
            <form action="" method="POST">
                <input type="text" name="nome" placeholder="Nome Completo" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="senha" placeholder="Senha" required>
                <button type="submit">Registrar</button>
            </form>
            <p class="error-message" style="color: red;">
                <?php if (isset($error)) echo htmlspecialchars($error); ?>
            </p>
            <p class="success-message" style="color: green;">
                <?php if (isset($success)) echo htmlspecialchars($success); ?>
            </p>
        </section>
    </main>

</body>
</html>
