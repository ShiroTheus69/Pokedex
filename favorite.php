<?php
session_start();
include("db.php");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$pokemon_id = $_GET['id'];
$pokemon_name = $_GET['name'];

// Verifica se já é favorito
$check = $conn->prepare("SELECT * FROM favorites WHERE user=? AND pokemon_id=?");
$check->bind_param("si", $user, $pokemon_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Remove favorito
    $delete = $conn->prepare("DELETE FROM favorites WHERE user=? AND pokemon_id=?");
    $delete->bind_param("si", $user, $pokemon_id);
    $delete->execute();
} else {
    // Adiciona favorito
    $insert = $conn->prepare("INSERT INTO favorites (user, pokemon_id, pokemon_name) VALUES (?,?,?)");
    $insert->bind_param("sis", $user, $pokemon_id, $pokemon_name);
    $insert->execute();
}

header("Location: index.php?id=$pokemon_id");
