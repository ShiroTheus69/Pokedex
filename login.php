<?php
session_start();
include("db.php");

// 🔹 Garantir que a tabela existe (mesmo truque do registro.php)
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, usuario, senha FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored = $row['senha'];

        // Verifica senha com hash
        if (password_verify($pass, $stored)) {
            $_SESSION['user'] = $row['usuario'];
            $_SESSION['user_id'] = $row['id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Usuário ou senha inválidos.";
        }
    } else {
        $error = "Usuário ou senha inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
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

    <?php if (!empty($error)) echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>'; ?>

    <form method="POST" action="login.php">
      <input type="text" name="username" placeholder="Usuário" required>
      <input type="password" name="password" placeholder="Senha" required>
      <button type="submit">Logar</button>
    </form>

    <p>Ainda não tem conta? <a href="registro.php">Registre-se aqui</a></p>
  </div>

</body>
</html>
