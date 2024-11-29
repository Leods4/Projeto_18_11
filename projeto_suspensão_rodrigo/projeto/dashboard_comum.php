<?php
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
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

// Reservar vaga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vaga_id'], $_POST['veiculo_id'])) {
    $vaga_id = $_POST['vaga_id'];
    $veiculo_id = $_POST['veiculo_id']; // O id do veículo selecionado pelo usuário

    // Verificar se a vaga está disponível
    $sql = "SELECT id, status FROM vagas WHERE id = ? AND status = 'disponivel'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vaga_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Criar uma reserva
        $sql_reserva = "INSERT INTO reservas (usuario_id, vaga_id, veiculo_id) VALUES (?, ?, ?)";
        $stmt_reserva = $conn->prepare($sql_reserva);
        $stmt_reserva->bind_param("iii", $usuario_id, $vaga_id, $veiculo_id);

        if ($stmt_reserva->execute()) {
            // Atualizar o status da vaga para 'ocupada'
            $sql_vaga = "UPDATE vagas SET status = 'ocupado' WHERE id = ?";
            $stmt_vaga = $conn->prepare($sql_vaga);
            $stmt_vaga->bind_param("i", $vaga_id);
            $stmt_vaga->execute();

            $success = "Vaga reservada com sucesso.";
        } else {
            $error = "Erro ao registrar a reserva. Tente novamente.";
        }

        $stmt_reserva->close();
    } else {
        $error = "A vaga selecionada não está disponível.";
    }

    $stmt->close();
}



// Listagem de veículos do usuário
$sql = "SELECT id, placa, marca, modelo, cor FROM veiculos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$veiculos = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consultar vagas disponíveis para reserva
$sql_vagas = "SELECT id, numero FROM vagas WHERE status = 'disponivel'";
$stmt_vagas = $conn->prepare($sql_vagas);
$stmt_vagas->execute();
$result_vagas = $stmt_vagas->get_result();
$vagas = $result_vagas->fetch_all(MYSQLI_ASSOC);
$stmt_vagas->close();

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
                <li><a href="logout.php">Sair</a></li>
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

            <h3>Reservar Vaga</h3>
<form action="" method="POST">
    <!-- Campo para selecionar a vaga -->
    <select name="vaga_id" required>
        <option value="">Selecione uma vaga</option>
        <?php 
        // Presumindo que você já tenha uma variável $vagas que contém as vagas disponíveis
        foreach ($vagas as $vaga): ?>
            <option value="<?php echo htmlspecialchars($vaga['id']); ?>">
                Vaga <?php echo htmlspecialchars($vaga['numero']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Campo para selecionar o veículo -->
    <select name="veiculo_id" required>
        <option value="">Selecione um veículo</option>
        <?php
        // Usando a variável $veiculos que já contém os veículos do usuário
        foreach ($veiculos as $veiculo): ?>
            <option value="<?php echo htmlspecialchars($veiculo['id']); ?>">
                <?php echo htmlspecialchars($veiculo['marca']); ?> - <?php echo htmlspecialchars($veiculo['modelo']); ?> (Placa: <?php echo htmlspecialchars($veiculo['placa']); ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Reservar Vaga</button>
</form>


        </section>
    </main>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
