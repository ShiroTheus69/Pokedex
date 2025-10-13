<?php
session_start();
include("db.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verifica se o arquivo foi enviado
if (isset($_FILES['nova_imagem']) && $_FILES['nova_imagem']['error'] === 0) {
    $extensao = strtolower(pathinfo($_FILES['nova_imagem']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extensao, $permitidas)) {
        $novo_nome = "perfil_" . $usuario_id . "." . $extensao;
        $pasta = "uploads/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }

        $caminho_final = $pasta . $novo_nome;

        if (move_uploaded_file($_FILES['nova_imagem']['tmp_name'], $caminho_final)) {
            // Atualiza o caminho da imagem no banco
            $sql = "UPDATE usuarios SET imagem_perfil = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $caminho_final, $usuario_id);
            $stmt->execute();

            header("Location: perfil.php");
            exit;
        } else {
            echo "Erro ao mover o arquivo.";
        }
    } else {
        echo "Formato inválido. Envie JPG, PNG ou GIF.";
    }
} else {
    echo "Nenhum arquivo enviado.";
}
?>
