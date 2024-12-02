<?php

require 'conexao.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] == 'suspenso') {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

function uploadFotoPerfil($foto) {
    // Diretório de upload
    $diretorio = "uploads/";

    $nomeImagem = uniqid() . "_" . basename($foto['name']);
    $caminhoImagem = $diretorio . $nomeImagem;

    $tipoImagem = mime_content_type($foto['tmp_name']);
    if (strpos($tipoImagem, 'image') === false) {
        return false;
    }

    if (move_uploaded_file($foto['tmp_name'], $caminhoImagem)) {
        return $caminhoImagem;
    }

    return false;
}

// Processar a atualização do nome
if (isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $sql = "UPDATE usuarios SET nome = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nome, $usuario_id);

    if ($stmt->execute()) {
        $success = "Nome atualizado com sucesso.";
    } else {
        $error = "Erro ao atualizar o nome.";
    }

    $stmt->close();
}

if (isset($_POST['telefone'])) {
    $telefone = $_POST['telefone'];
    $sql = "UPDATE usuarios SET telefone = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $telefone, $usuario_id);

    if ($stmt->execute()) {
        $success = "Telefone atualizado com sucesso.";
    } else {
        $error = "Erro ao atualizar o telefone.";
    }

    $stmt->close();
}

if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
    $foto_perfil = uploadFotoPerfil($_FILES['foto_perfil']);
    if ($foto_perfil) {
        $sql = "UPDATE usuarios SET foto_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $foto_perfil, $usuario_id);

        if ($stmt->execute()) {
            $success = "Foto de perfil atualizada com sucesso.";
        } else {
            $error = "Erro ao atualizar a foto de perfil.";
        }

        $stmt->close();
    } else {
        $error = "Falha ao fazer upload da foto. Verifique o formato da imagem.";
    }
}

$conn->close();

header("Location: perfil.php");
exit;
?>
