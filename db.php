<?php
$host = "localhost";
$user = "root";  // usuário padrão do XAMPP
$pass = "";      // senha em branco (se você não mudou)
$db   = "pokedex"; // nome do banco que você criou

// Conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verificar erros
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
