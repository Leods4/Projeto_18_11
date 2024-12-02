<?php

require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'administrador') {
    header("Location: index.php");
    exit;
}

// Função para liberar vaga
if (isset($_GET['liberar_vaga'])) {
    $vaga_id = intval($_GET['liberar_vaga']);
    $stmt = $conn->prepare("UPDATE vagas SET status = 'disponivel', veiculo_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $vaga_id);
    $stmt->execute();
    $stmt->close();
}

// Função para excluir vaga
if (isset($_GET['excluir_vaga'])) {
    $vaga_id = intval($_GET['excluir_vaga']);
    // Excluir a vaga do banco de dados
    $stmt = $conn->prepare("DELETE FROM vagas WHERE id = ?");
    $stmt->bind_param("i", $vaga_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirecionar para a mesma página para refletir as alterações
    header("Location: dashboard_admin.php");
    exit;
}

// Adicionar vagas em lote
if (isset($_POST['add_vagas'])) {
    $inicio_vaga = intval($_POST['inicio_vaga']);
    $fim_vaga = intval($_POST['fim_vaga']);

    // Preparar o statement para adicionar as vagas
    $stmt = $conn->prepare("INSERT INTO vagas (numero, status) VALUES (?, 'disponivel')");

    for ($numero_vaga = $inicio_vaga; $numero_vaga <= $fim_vaga; $numero_vaga++) {
        $stmt->bind_param("i", $numero_vaga);
        $stmt->execute();
    }
    $stmt->close();
}

// Consulta para obter as vagas e os usuários
$sql = "SELECT v.id, v.numero, v.status, u.nome AS usuario_nome 
        FROM vagas v
        LEFT JOIN reservas r ON r.vaga_id = v.id
        LEFT JOIN veiculos veic ON r.veiculo_id = veic.id
        LEFT JOIN usuarios u ON veic.usuario_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Mapa de Vagas</title>
    <link rel="stylesheet" href="style.css">
    <style>
        

.dashboard h2 {
    color: #4a90e2;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    background-color: white;
    border: 1px solid #ddd;
}

table th, table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #4a90e2;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

table tr:hover {
    background-color: #eaf3fc;
}

a {
    color: #4a90e2;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

form {
    background-color: white;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

form label {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

form input, form button {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

form button {
    background-color: #4a90e2;
    color: white;
    border: none;
    cursor: pointer;
}

form button:hover {
    background-color: #357abd;
}

footer {
    text-align: center;
    padding: 10px;
    background-color: #4a90e2;
    color: white;
    position: relative;
    bottom: 0;
    width: 100%;
}

    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
            <img src="icone.png" alt="Logo" class="header-logo">
                <li><a href="index.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="suspensao.php">Suspensão</a></li>
                <li><a href="?action=logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard">
            <h2>Mapa de Vagas</h2>
            <table>
                <tr>
                    <th>Número da Vaga</th>
                    <th>Status</th>
                    <th>Reservado por</th>
                    <th>Ações</th>
                </tr>
                <?php 
                // Mostrar vagas do banco de dados
                if ($result && $result->num_rows > 0) {
                    while ($vaga = $result->fetch_assoc()) {
                ?>
                    <tr>
                        <td><?php echo $vaga['numero']; ?></td>
                        <td><?php echo $vaga['status'] === 'disponivel' ? 'Disponível' : 'Ocupada'; ?></td>
                        <td><?php echo $vaga['usuario_nome'] ? $vaga['usuario_nome'] : 'N/A'; ?></td>
                        <td>
                            <?php if ($vaga['status'] === 'ocupada'): ?>
                                <a href="?liberar_vaga=<?php echo $vaga['id']; ?>" onclick="return confirm('Tem certeza que deseja liberar esta vaga?');">Liberar</a>
                            <?php else: ?>
                                <a href="?excluir_vaga=<?php echo $vaga['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir esta vaga?');">Excluir</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='4'>Nenhuma vaga encontrada.</td></tr>";
                }
                ?>
            </table>

            <h3>Adicionar Vagas em Lote</h3>
            <form action="" method="POST">
                <label for="inicio_vaga">Número inicial:</label>
                <input type="number" name="inicio_vaga" required>
                <br>
                <label for="fim_vaga">Número final:</label>
                <input type="number" name="fim_vaga" required>
                <button type="submit" name="add_vagas">Adicionar Vagas</button>
            </form>
        </section>
        <br><br><br><br> 
    </main>

</body>
</html>

<?php
$conn->close();
?>
