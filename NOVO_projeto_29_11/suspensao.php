<?php
require 'conexao.php';

// Verifica se a sessão já foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Inicia a sessão se não estiver ativa
}

$mensagem = "";

// Verifica se o usuário é um administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

// Verifica se o parâmetro de ação foi passado (suspender ou reverter suspensão)
if (isset($_GET['id'])) {
    $usuario_id = $_GET['id'];

    // Se a ação for "suspender", atualiza o tipo do usuário para 'suspenso'
    if (isset($_GET['acao']) && $_GET['acao'] == 'suspender') {
        // Atualiza o tipo para suspenso
        $sql = "UPDATE usuarios SET tipo = 'suspenso' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $mensagem = "Usuário suspenso com sucesso!";
        } else {
            $mensagem = "Erro ao suspender o usuário: " . $stmt->error;  // Exibe erro SQL
        }
    }
    // Se a ação for "reverter", atualiza o tipo do usuário para 'comum'
    elseif (isset($_GET['acao']) && $_GET['acao'] == 'reverter') {
        // Atualiza o tipo para comum
        $sql = "UPDATE usuarios SET tipo = 'comum' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            $mensagem = "Suspensão revertida com sucesso!";
        } else {
            $mensagem = "Erro ao reverter a suspensão do usuário: " . $stmt->error;  // Exibe erro SQL
        }
    }
}

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
    <title>Gestão de Suspensão de Usuários</title>
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

        <!-- Exibe a mensagem de sucesso ou erro -->
        <?php if ($mensagem): ?>
        <div>
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>  <!-- Exibe o tipo do usuário -->
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Exibe os dados de cada usuário
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $tipo = $row['tipo'];  // Verifica o tipo do usuário
                        echo "<tr>
                                <td>" . $row['id'] . "</td>
                                <td>" . $row['nome'] . "</td>
                                <td>" . $row['email'] . "</td>
                                <td>" . ucfirst($tipo) . "</td>  <!-- Exibe o tipo do usuário -->
                                <td>";
                                    // Se o usuário não estiver suspenso, exibe a opção de suspender
                                    if ($tipo != 'suspenso') {
                                        echo "<a href='suspensao.php?id=" . $row['id'] . "&acao=suspender' onclick='return confirm(\"Tem certeza que deseja suspender este usuário?\")'>Suspender</a>";
                                    } else {
                                        // Se o usuário estiver suspenso, exibe a opção de reverter a suspensão
                                        echo "<a href='suspensao.php?id=" . $row['id'] . "&acao=reverter' onclick='return confirm(\"Tem certeza que deseja reverter a suspensão deste usuário?\")'>Reverter Suspensão</a>";
                                    }
                        echo "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Nenhum usuário encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
