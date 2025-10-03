<?php
session_start();
include("db.php"); // conexão MySQL

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

    // Verificar se o usuário já existe
    $check = $conn->prepare("SELECT id FROM usuarios WHERE usuario=?");
    $check->bind_param("s", $user);
    $check->execute();
    $result = $check->get_result();

    if ($result && $result->num_rows > 0) {
        $error = "Este nome de usuário já está em uso.";
    } else {
        // Cria hash seguro da senha
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Inserir novo usuário
        $query = $conn->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
        $query->bind_param("ss", $user, $hash);

        if ($query->execute()) {
            $success = "Conta criada com sucesso! <a href='login.php'>Clique aqui para logar</a>";
        } else {
            $error = "Erro ao registrar. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Registrar Novo Mestre Pokémon</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>

  <div class="login-container">
    <div class="lights">
      <div class="light red"></div>
      <div class="light yellow"></div>
      <div class="light green"></div>
    </div>

    <h1>Crie sua Conta</h1>
    <p>Registre-se para começar sua jornada Pokémon!</p>

    <?php if (!empty($error)) echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color:green;'>" . $success . "</p>"; ?>

    <form method="POST" action="registro.php">
      <input type="text" name="username" placeholder="Novo Usuário" required>
      <input type="password" name="password" placeholder="Senha" required>
      <button type="submit">Registrar</button>
    </form>

    <p>Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
  </div>

</body>
</html>
