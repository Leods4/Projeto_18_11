<?php
include 'db.php';

// Verifica se o usuário está logado e é um aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verifica se o usuário já tem uma reserva
$sql_check_reserva = "SELECT * FROM reservas WHERE usuario_id = ? AND status = 'reservado'";
$stmt_check_reserva = $conn->prepare($sql_check_reserva);
if (!$stmt_check_reserva) {
    die("Erro ao preparar consulta: " . $conn->error);
}
$stmt_check_reserva->bind_param("i", $usuario_id);
$stmt_check_reserva->execute();
$result_check_reserva = $stmt_check_reserva->get_result();

if ($result_check_reserva->num_rows > 0) {
    echo "<script>alert('Você já tem uma vaga reservada.');</script>";
} else {
    // Reservar uma vaga
    if (isset($_POST['reservar_vaga'])) {
        $vaga_id = intval($_POST['vaga_id']);
        $veiculo_id = intval($_POST['veiculo_id']);

        // Verifica se a vaga está disponível
        $stmt = $conn->prepare("UPDATE vagas SET status = 'ocupada', veiculo_id = ? WHERE id = ? AND status = 'disponivel'");
        if (!$stmt) {
            die("Erro ao preparar consulta: " . $conn->error);
        }
        $stmt->bind_param("ii", $veiculo_id, $vaga_id);

        if ($stmt->execute()) {
            // Registra a reserva na tabela reservas
            $stmt_reserva = $conn->prepare("INSERT INTO reservas (usuario_id, vaga_id, status) VALUES (?, ?, 'reservado')");
            $stmt_reserva->bind_param("ii", $usuario_id, $vaga_id);
            $stmt_reserva->execute();

            echo "<script>alert('Vaga reservada com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao reservar a vaga.');</script>";
        }

        $stmt->close();
    }
}

// Cadastro de veículo
if (isset($_POST['cadastrar_veiculo'])) {
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $cor = $_POST['cor'];
    $tipo = $_POST['tipo'];

    $stmt_cadastrar_veiculo = $conn->prepare("INSERT INTO veiculos (usuario_id, placa, marca, cor, tipo) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt_cadastrar_veiculo) {
        die("Erro ao preparar consulta: " . $conn->error);
    }
    $stmt_cadastrar_veiculo->bind_param("issss", $usuario_id, $placa, $marca, $cor, $tipo);

    if ($stmt_cadastrar_veiculo->execute()) {
        echo "<script>alert('Veículo cadastrado com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar veículo.');</script>";
    }

    $stmt_cadastrar_veiculo->close();
}

$stmt_check_reserva->close();

// Cancelar uma reserva
if (isset($_POST['cancelar_reserva'])) {
    $vaga_id = intval($_POST['vaga_id']);

    $stmt_check_user = $conn->prepare("SELECT * FROM reservas WHERE vaga_id = ? AND usuario_id = ?");
    $stmt_check_user->bind_param("ii", $vaga_id, $usuario_id);
    $stmt_check_user->execute();
    $result_check_user = $stmt_check_user->get_result();

    if ($result_check_user->num_rows > 0) {
        $stmt_cancelar_reserva = $conn->prepare("UPDATE reservas SET status = 'cancelado' WHERE vaga_id = ? AND usuario_id = ?");
        $stmt_cancelar_reserva->bind_param("ii", $vaga_id, $usuario_id);
        $stmt_cancelar_reserva->execute();

        $stmt_atualizar_vaga = $conn->prepare("UPDATE vagas SET status = 'disponivel', veiculo_id = NULL WHERE id = ?");
        $stmt_atualizar_vaga->bind_param("i", $vaga_id);
        $stmt_atualizar_vaga->execute();

        echo "<script>alert('Reserva cancelada com sucesso!');</script>";
    } else {
        echo "<script>alert('Você não tem uma reserva para esta vaga.');</script>";
    }

    $stmt_check_user->close();
    $stmt_cancelar_reserva->close();
    $stmt_atualizar_vaga->close();
}

// Consulta para obter as vagas
$sql = "SELECT v.id, v.numero, v.status, r.usuario_id as reservado_por FROM vagas v LEFT JOIN reservas r ON v.id = r.vaga_id AND r.status = 'reservado'";
$result_vagas = $conn->query($sql);

// Consulta para obter os veículos do usuário
$sql_veiculos = "SELECT id, placa, marca, cor, tipo FROM veiculos WHERE usuario_id = ?";
$stmt_veiculos = $conn->prepare($sql_veiculos);
$stmt_veiculos->bind_param("i", $_SESSION['usuario_id']);
$stmt_veiculos->execute();
$result_veiculos = $stmt_veiculos->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Aluno - Reservar Vaga</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
    <nav>
            <ul>
                <li><a href="inicio.php">Início</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <!-- Vagas Disponíveis -->
            <h3>Vagas Disponíveis</h3>
            <table>
                <tr>
                    <th>Número da Vaga</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
                <?php while ($vaga = $result_vagas->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $vaga['numero']; ?></td>
                        <td>
                            <?php 
                                if ($vaga['status'] === 'disponivel') {
                                    echo 'Disponível';
                                } elseif ($vaga['reservado_por'] == $usuario_id) {
                                    echo 'Reservado por você';
                                } else {
                                    echo 'Ocupada';
                                }
                            ?>
                        </td>
                        <td>
                            <?php if ($vaga['status'] === 'disponivel'): ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="vaga_id" value="<?php echo $vaga['id']; ?>">
                                    <select name="veiculo_id" required>
                                        <option value="" disabled selected>Selecione um veículo</option>
                                        <?php
                                        $result_veiculos->data_seek(0); // Reseta o cursor do resultado para listar veículos
                                        while ($veiculo = $result_veiculos->fetch_assoc()):
                                        ?>
                                            <option value="<?php echo $veiculo['id']; ?>">
                                                <?php echo $veiculo['placa']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="reservar_vaga">Reservar</button>
                                </form>
                            <?php elseif ($vaga['reservado_por'] == $usuario_id): ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="vaga_id" value="<?php echo $vaga['id']; ?>">
                                    <button type="submit" name="cancelar_reserva">Cancelar Reserva</button>
                                </form>
                            <?php else: ?>
                                <button disabled>Ocupada</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Cadastrar Veículo -->
            <h3>Cadastrar Veículo</h3>
            <form action="" method="POST">
                <input type="text" name="placa" placeholder="Placa" required>
                <input type="text" name="marca" placeholder="Marca" required>
                <input type="text" name="cor" placeholder="Cor" required>
                <select name="tipo" required>
                    <option value="carro">Carro</option>
                    <option value="moto">Moto</option>
                </select>
                <button type="submit" name="cadastrar_veiculo">Cadastrar Veículo</button>
            </form>

            <!-- Meus Veículos -->
            <h3>Meus Veículos</h3>
            <table>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Cor</th>
                    <th>Tipo</th>
                </tr>
                <?php
                $result_veiculos->data_seek(0); // Reseta o cursor novamente para listar os veículos
                while ($veiculo = $result_veiculos->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $veiculo['placa']; ?></td>
                        <td><?php echo $veiculo['marca']; ?></td>
                        <td><?php echo $veiculo['cor']; ?></td>
                        <td><?php echo $veiculo['tipo']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </main>
</body>
</html>


<?php
$conn->close();
$stmt_veiculos->close();
?>
