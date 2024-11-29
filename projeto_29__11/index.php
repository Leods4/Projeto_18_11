<?php
require 'conexao.php';

if (!isset($_SESSION['tipo_usuario'])) {
    $_SESSION['tipo_usuario'] = 'guest';
}

// Consulta para obter as vagas do mapa com segurança
$sql = "
    SELECT 
        vagas.numero, 
        vagas.status, 
        veiculos.placa, 
        veiculos.marca 
    FROM vagas 
    LEFT JOIN reservas ON reservas.vaga_id = vagas.id 
    LEFT JOIN veiculos ON reservas.veiculo_id = veiculos.id";
$result = $conn->query($sql);
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status = $row['status'];
                        $veiculo = $status === 'ocupado' ? "{$row['placa']} ({$row['marca']})" : "Disponível";
                        $class = $status === 'ocupado' ? 'vaga-ocupada' : 'vaga-disponivel';
                        echo "<div class='vaga $class'>Vaga {$row['numero']}<br>$veiculo</div>";
                    }
                } else {
                    echo "<p>Sem vagas disponíveis no momento.</p>";
                }
                ?>
            </div>
        </section>
    </main>

    <footer>
        <p>© 2024 Sistema de Estacionamento. Todos os direitos reservados.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>
