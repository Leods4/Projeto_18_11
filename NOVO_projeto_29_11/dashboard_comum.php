<?php
require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] == 'suspenso') {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$success = $error = null;

// Registro de veículo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['placa'])) {
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $cor = $_POST['cor'];

    if (empty($placa) || empty($marca) || empty($modelo) || empty($cor)) {
        $error = "Por favor, preencha todos os campos para registrar o veículo.";
    } else {
        $sql = "INSERT INTO veiculos (usuario_id, placa, marca, modelo, cor) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $usuario_id, $placa, $marca, $modelo, $cor);

        if ($stmt->execute()) {
            $success = "Veículo registrado com sucesso.";
        } else {
            $error = "Erro ao registrar veículo. Tente novamente.";
        }

        $stmt->close();
    }
}

// Listagem de veículos do usuário
$sql = "SELECT id, placa, marca, modelo, cor FROM veiculos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$veiculos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Estacionamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="dashboard_comum.php">Dashboard</a></li>
                <li><a href="?action=logout">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="dashboard">
            <h2>Bem-vindo ao Dashboard</h2>

            <!-- Registro de Veículo -->
            <h3>Registrar Veículo</h3>
            <form action="" method="POST">
                <input type="text" name="placa" placeholder="Placa do Veículo" required>
                <input type="text" name="marca" placeholder="Marca do Veículo" required>
                <input type="text" name="modelo" placeholder="Modelo do Veículo" required>
                <input type="text" name="cor" placeholder="Cor do Veículo" required>
                <button type="submit">Registrar</button>
            </form>
            <?php if ($error): ?>
                <p class="error-message" style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success-message" style="color: green;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <!-- Listagem de Veículos -->
            <h3>Meus Veículos</h3>
            <?php if (count($veiculos) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Placa</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Cor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($veiculos as $veiculo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($veiculo['placa']); ?></td>
                                <td><?php echo htmlspecialchars($veiculo['marca']); ?></td>
                                <td><?php echo htmlspecialchars($veiculo['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($veiculo['cor']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Você ainda não registrou nenhum veículo.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
