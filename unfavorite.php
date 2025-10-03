<?php
session_start();
include("db.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$pokemon_nome = $_POST['pokemon_nome'] ?? null;

if ($pokemon_nome) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND pokemon_nome = ?");
    $stmt->bind_param("is", $usuario_id, $pokemon_nome);
    $stmt->execute();
}

header("Location: perfil.php");
exit;