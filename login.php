<?php
session_start();
include("db.php"); // conexão MySQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $query->bind_param("ss", $user, $pass);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pokedex Login</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

  <div class="login-container">
    <div class="lights">
      <div class="light red"></div>
      <div class="light yellow"></div>
      <div class="light green"></div>
    </div>

    <h1>Login Treinador</h1>
    <form method="POST" action="login_process.php">
      <input type="text" name="usuario" placeholder="Usuario" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit">Logar</button>
    </form>
  </div>

</body>
</html>

