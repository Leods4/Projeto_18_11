<?php
session_start();  // Certifique-se de que a sessão está iniciada.

require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Buscar os dados do usuário
$sql = "SELECT nome, email, telefone, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nome, $email, $telefone, $foto_perfil);
$stmt->fetch();

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Sistema de Estacionamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <?php if ($_SESSION['tipo_usuario'] === 'administrador'): ?>
                <li><a href="index.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="suspensao.php">Suspensão</a></li>
                <li><a href="?action=logout">Sair</a></li>
                <?php elseif ($_SESSION['tipo_usuario'] === 'comum'): ?>
                <li><a href="index.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="dashboard_comum.php">Dashboard</a></li>
                <li><a href="logout.php">Sair</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="perfil">
            <h2>Perfil do Usuário</h2>
            <div>
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($nome); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($telefone) ? htmlspecialchars($telefone) : 'Não informado'; ?></p>
                <p><strong>Foto de Perfil:</strong> 
                    <?php 
                    if ($foto_perfil) {
                        echo '<img src="' . htmlspecialchars($foto_perfil) . '" alt="Foto de Perfil">';
                    } else {
                        echo 'Não informada';
                    }
                    ?>
                </p>
            </div>

            <!-- Formulário para atualizar o nome -->
            <h3>Atualizar Nome</h3>
            <form action="atualizar_perfil.php" method="POST">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                <button type="submit">Salvar Nome</button>
            </form>

            <!-- Formulário para atualizar a foto de perfil -->
            <h3>Atualizar Foto de Perfil</h3>
            <form action="atualizar_perfil.php" method="POST" enctype="multipart/form-data">
                <label for="foto_perfil">Foto:</label>
                <input type="file" name="foto_perfil" accept="image/*">
                <button type="submit">Salvar Foto</button>
            </form>

            <!-- Formulário para atualizar o telefone -->
            <h3>Atualizar Telefone</h3>
            <form action="atualizar_perfil.php" method="POST">
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
                <button type="submit">Salvar Telefone</button>
            </form>

            <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
            <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        </section>
    </main>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
