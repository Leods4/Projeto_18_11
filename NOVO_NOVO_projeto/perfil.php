<?php
session_start();  // Certifique-se de que a sessão está iniciada.

require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] == 'suspenso') {
    header("Location: index.php");
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        main {
            padding: 20px;
        }

        .perfil {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        h2, h3, h4 {
            color: #4a90e2;
        }

        .foto-perfil {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #4a90e2;
        }

        .perfil div {
            margin-bottom: 20px;
        }

        form {
            background-color: #f9f9f9;
            padding: 15px;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        form label {
            font-weight: bold;
        }

        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #4682B4;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #3a6ea5;
        }

        .alert {
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
            <img src="icone.png" alt="Logo" class="header-logo">
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
                <p><strong>Foto de Perfil:</strong><br>
                    <?php 
                    if ($foto_perfil) {
                        echo '<img src="' . htmlspecialchars($foto_perfil) . '" alt="Foto de Perfil" class="foto-perfil">';
                    } else {
                        echo 'Não informada';
                    }
                    ?>
                </p>
            </div>

            <h4>Atualizar Nome</h4>
            <form action="atualizar_perfil.php" method="POST">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                <button type="submit">Salvar Nome</button>
            </form>

            <h3>Atualizar Foto de Perfil</h3>
            <form action="atualizar_perfil.php" method="POST" enctype="multipart/form-data">
                <label for="foto_perfil">Foto:</label>
                <input type="file" name="foto_perfil" accept="image/*">
                <button type="submit">Salvar Foto</button>
            </form>

            <h3>Atualizar Telefone</h3>
            <form action="atualizar_perfil.php" method="POST">
                <label for="telefone">Telefone:</label>
                <input type="text" name="telefone" value="<?php echo htmlspecialchars($telefone); ?>" required>
                <button type="submit">Salvar Telefone</button>
            </form>

            <?php if (isset($error)) echo "<div class='alert alert-error'>$error</div>"; ?>
            <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        </section>
    </main>

</body>
</html>
