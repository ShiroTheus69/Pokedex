<?php
session_start();
include("db.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pokemon_nome = $_GET['name'] ?? null;

if ($pokemon_nome) {
    // Verifica se já está favoritado
    $check = $conn->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND pokemon_nome = ?");
    $check->bind_param("is", $usuario_id, $pokemon_nome);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {
        // Insere como favorito
        $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, pokemon_nome) VALUES (?, ?)");
        $stmt->bind_param("is", $usuario_id, $pokemon_nome);
        $stmt->execute();
    }
}

// Redireciona de volta para a página principal
header("Location: index.php");
exit;