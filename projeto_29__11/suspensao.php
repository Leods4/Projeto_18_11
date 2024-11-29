<?php

require 'conexao.php';

$mensagem="";

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

// Verifica se o parâmetro ID foi passado na URL
if (isset($_GET['id'])) {
    // Obtém o ID do usuário que será suspenso
    $usuario_id = $_GET['id'];

    // Atualiza o status do usuário para 'suspenso'
    $sql = "UPDATE usuarios SET tipo = 'suspenso' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);

    if ($stmt->execute()) {
        // Redireciona para a página de administração após a atualização
        header("Location: index.php");  // Redireciona para index.php após a atualização
        exit;
    } else {
        $mensagem="Erro ao suspender o usuário.";
    }

    // Fecha a conexão
    $conn->close();
} else {
    $mensagem="ID do usuário não fornecido.";
}
?>

<?php

// Consulta para buscar todos os usuários
$sql = "SELECT id, nome, email, tipo FROM usuarios";
$result = $conn->query($sql);

// Fecha a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Estacionamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="suspensao.php">Suspensão</a></li>
                <li><a href="?action=logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Gestão de Usuários</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Exibe os dados de cada usuário
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status = ($row['tipo'] == 'suspenso') ? 'Suspenso' : 'Ativo'; // Verifica o status do usuário
                        echo "<tr>
                                <td>" . $row['id'] . "</td>
                                <td>" . $row['nome'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . $status . "</td>
                                <td>
                                    <!-- Ação de suspender o usuário -->
                                    <a href='suspender_usuario.php?id=" . $row['id'] . "' onclick='return confirm(\"Tem certeza que deseja suspender este usuário?\")'>Suspender</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nenhum usuário encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <?php if ($mensagem): ?>
    <div>
        <?php echo $mensagem; ?>
    </div>
    <?php endif; ?>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
