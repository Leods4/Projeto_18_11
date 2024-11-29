<?php
require 'conexao.php';

if (!isset($_SESSION['tipo_usuario'])) {
    $_SESSION['tipo_usuario'] = 'guest';
}

$liked = isset($_COOKIE['liked']) && $_COOKIE['liked'] == 'true';

// Variáveis de feedback
$error = '';
$success = '';

// Consulta para obter as vagas e veículos
$sql_vagas = "
    SELECT 
        vagas.id, 
        vagas.numero, 
        vagas.status, 
        reservas.veiculo_id, 
        veiculos.placa, 
        veiculos.marca, 
        reservas.data_expiracao
    FROM vagas 
    LEFT JOIN reservas ON reservas.vaga_id = vagas.id 
    LEFT JOIN veiculos ON reservas.veiculo_id = veiculos.id
";
$result_vagas = $conn->query($sql_vagas);

// Variável do usuário (se o usuário for do tipo "comum")
$usuario_id = null;
$veiculos_usuario = [];
if ($_SESSION['tipo_usuario'] === 'comum') {
    // Definindo $usuario_id para usuários comuns
    $usuario_id = $_SESSION['usuario_id']; 
    $sql_veiculos = "SELECT id, placa, marca, modelo FROM veiculos WHERE usuario_id = $usuario_id";
    $result_veiculos = $conn->query($sql_veiculos);
    while ($row = $result_veiculos->fetch_assoc()) {
        $veiculos_usuario[] = $row;
    }
}

// Lógica de reserva de vaga (se o formulário for submetido)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vaga_id'], $_POST['veiculo_id'])) {
    $vaga_id = $_POST['vaga_id'];
    $veiculo_id = $_POST['veiculo_id'];

    // Verificar se o veículo já está em uma vaga ocupada
    $sql_check_veiculo_reserva = "SELECT * FROM reservas WHERE veiculo_id = $veiculo_id AND vaga_id IS NOT NULL";
    $result_check_veiculo_reserva = $conn->query($sql_check_veiculo_reserva);

    if ($result_check_veiculo_reserva->num_rows > 0) {
        // O veículo já tem uma reserva ativa
        $error = "Este veículo já está reservado em outra vaga. Não é possível realizar outra reserva.";
    } else {
        // Verificar se a vaga está disponível
        $sql_check_vaga = "SELECT status FROM vagas WHERE id = $vaga_id";
        $result_check_vaga = $conn->query($sql_check_vaga);
        $vaga = $result_check_vaga->fetch_assoc();

        if ($vaga && $vaga['status'] === 'disponivel') {
            // Reservar a vaga (atualizando a tabela reservas)
            $data_expiracao = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Definindo um tempo de expiração de 30 minutos
            $sql_reserva = "INSERT INTO reservas (vaga_id, veiculo_id, data_expiracao) VALUES ($vaga_id, $veiculo_id, '$data_expiracao')";
            if ($conn->query($sql_reserva)) {
                // Atualizar o status da vaga para ocupado
                $sql_update_vaga = "UPDATE vagas SET status = 'ocupado' WHERE id = $vaga_id";
                $conn->query($sql_update_vaga);

                // Definir mensagem de sucesso
                $success = "Vaga reservada com sucesso!";
                // Redirecionar para recarregar a página
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                // Definir mensagem de erro
                $error = "Erro ao realizar a reserva. Tente novamente mais tarde.";
            }
        } else {
            // Definir mensagem de erro se a vaga estiver ocupada
            $error = "Esta vaga já está ocupada. Por favor, escolha outra.";
        }
    }
}

// Lógica para cancelar a reserva (se o usuário for o proprietário da reserva)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_vaga_id'])) {
    $vaga_id = $_POST['cancelar_vaga_id'];

    // Verificar se o usuário é o proprietário da reserva
    $sql_check_reserva = "
        SELECT reservas.id, reservas.veiculo_id 
        FROM reservas
        INNER JOIN veiculos ON veiculos.id = reservas.veiculo_id
        WHERE reservas.vaga_id = $vaga_id AND veiculos.usuario_id = $usuario_id
    ";
    $result_check_reserva = $conn->query($sql_check_reserva);

    if ($result_check_reserva->num_rows > 0) {
        // Cancelar a reserva (remover o registro de reserva)
        $sql_cancelar_reserva = "DELETE FROM reservas WHERE vaga_id = $vaga_id";
        if ($conn->query($sql_cancelar_reserva)) {
            // Atualizar o status da vaga para "disponível"
            $sql_update_vaga = "UPDATE vagas SET status = 'disponivel' WHERE id = $vaga_id";
            $conn->query($sql_update_vaga);

            $success = "Reserva cancelada com sucesso!";
            // Redirecionar para recarregar a página
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Erro ao cancelar a reserva. Tente novamente mais tarde.";
        }
    } else {
        $error = "Você não tem uma reserva nesta vaga.";
    }
}

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
                    <li><a href="?action=logout">Sair</a></li>
                <?php else: ?>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="registro.php">Criar Conta</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Bem-vindo ao Sistema de Estacionamento</h1>
            <p>Gerencie suas vagas e veículos de forma fácil e rápida.</p>
        </section>

        <!-- Mapa de Estacionamento -->
        <section class="mapa-estacionamento">
            <h2>Mapa do Estacionamento</h2>
            <div class="estacionamento">
                <?php
                if ($result_vagas->num_rows > 0) {
                    while ($row = $result_vagas->fetch_assoc()) {
                        $status = $row['status'];
                        $veiculo = $status === 'ocupado' ? "{$row['placa']} ({$row['marca']})" : "Disponível";
                        $class = $status === 'ocupado' ? 'vaga-ocupada' : 'vaga-disponivel';

                        // Se a vaga está ocupada e o usuário for o dono da reserva, ele pode cancelar
                        if ($status === 'ocupado' && $row['veiculo_id'] == $usuario_id) {
                            $data_expiracao = new DateTime($row['data_expiracao']);
                            $hora_disponivel = $data_expiracao->format('d/m/Y H:i:s'); // Formatar como 'dia/mês/ano hora:minuto:segundo'
                            
                            echo "<form action='' method='POST'>
                                    <input type='hidden' name='cancelar_vaga_id' value='{$row['id']}'>
                                    <button type='submit' class='vaga $class'>Vaga {$row['numero']} - Liberada em: {$hora_disponivel} - Cancelar Reserva</button>
                                  </form>";
                        } elseif ($_SESSION['tipo_usuario'] === 'comum') {
                            // Exibir a opção de reserva apenas para usuários comuns
                            echo "<form action='' method='POST'>
                                    <input type='hidden' name='vaga_id' value='{$row['id']}'>
                                    <button type='button' class='vaga $class' onclick='mostrarVeiculosForm({$row['id']})'>Vaga {$row['numero']}</button>
                                    <div class='veiculo-form' id='veiculo-form-{$row['id']}' style='display:none;'>
                                        <select name='veiculo_id' required>
                                            <option value=''>Selecione um veículo</option>";
                                            foreach ($veiculos_usuario as $veiculo) {
                                                echo "<option value='{$veiculo['id']}'>{$veiculo['marca']} - {$veiculo['modelo']} (Placa: {$veiculo['placa']})</option>";
                                            }
                            echo "</select>
                                    <button type='submit' class='btn-reservar'>Reservar</button>
                                  </div>
                                  </form>";
                        } else {
                            echo "<div class='vaga $class'>Vaga {$row['numero']} - {$veiculo}</div>";
                        }
                    }
                } else {
                    echo "<p>Não há vagas disponíveis no momento.</p>";
                }
                ?>
            </div>
        </section>

        <!-- Mensagens de erro e sucesso -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
    </main>

    <script>
        // Função para mostrar o formulário de seleção de veículos
        function mostrarVeiculosForm(vagaId) {
            var form = document.getElementById('veiculo-form-' + vagaId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>

<?php if (!$liked): ?>
    <!-- Like Button (visible only if the user hasn't clicked it yet) -->
    <form method="POST">
        <button type="submit" name="like_button">Curtir</button>
    </form>
<?php else: ?>
    <!-- Message after the user clicks the button -->
    <p>Obrigado pelo feedback!</p>
<?php endif; ?>

<?php
// Handle the button click and set the cookie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_button'])) {
    // Set the cookie for 30 days
    setcookie('liked', 'true', time() + (30 * 24 * 60 * 60), '/');  // Expires in 30 days
    // Refresh the page so the cookie is available
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

</body>
</html>
