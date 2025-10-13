<?php
session_start();
include("db.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nome'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$name = $_SESSION['usuario_nome'];

// Busca imagem personalizada, se existir
$sql = "SELECT imagem_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!empty($row['imagem_perfil']) && file_exists($row['imagem_perfil'])) {
    $image = $row['imagem_perfil'];
} else {
    $image = "image/hilda.jpg"; // imagem padrão
}

// Buscar Pokémon favoritados
$favoritos = [];
$sql = "SELECT pokemon_nome FROM favoritos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $favoritos[] = $row['pokemon_nome'];
}
?>

<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Pokédex - <?= htmlspecialchars($name) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* === Estilo da imagem de perfil === */
        .screen {
            width: 180px;
            height: 180px;
            overflow: hidden;
            border-radius: 12px;
            border: 3px solid #d62828;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.4);
        }

        .screen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* === Upload abaixo da imagem === */
        .upload-area {
            text-align: center;
            margin-top: 12px;
        }

        .upload-area label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #fff;
            text-shadow: 1px 1px 2px #000;
        }

        .upload-area input[type="file"] {
            display: none;
        }

        /* Botão estilizado */
        .upload-btn {
            display: inline-block;
            background-color: #ffcb05;
            color: #2a75bb;
            font-weight: bold;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }

        .upload-btn:hover {
            background-color: #f8b700;
            transform: scale(1.05);
        }

        .submit-btn {
            display: inline-block;
            background-color: #2a75bb;
            color: #fff;
            font-weight: bold;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            margin-left: 8px;
            font-size: 14px;
        }

        .submit-btn:hover {
            background-color: #1d5e9b;
            transform: scale(1.05);
        }

        /* === Geral === */
        body {
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
        }

        .info {
            text-align: center;
            margin-top: 10px;
            color: #fff;
            text-shadow: 1px 1px 3px #000;
        }
    </style>
</head>

<body>
    <button id="changeBgBtn">Mudar Fundo</button>
    <button id="toggleAnimation">Parar/Continuar Animação</button>
    <a href="index.php"><button id="voltar">Retornar para a pokedex</button></a>

    <div class="pokedex">
        <div class="left">
            <div class="lights">
                <span class="light blue main-button"></span>
                <div class="side-buttons">
                    <span class="light red"></span>
                    <span class="light yellow"></span>
                    <span class="light green"></span>
                </div>
            </div>

            <!-- Imagem de perfil -->
            <div class="screen">
                <img src="<?= htmlspecialchars($image) ?>" alt="Imagem do usuário">
            </div>

            <!-- Formulário abaixo da imagem -->
            <div class="upload-area">
                <form id="uploadForm" action="upload_imagem.php" method="post" enctype="multipart/form-data">
                    <label for="nova_imagem">Alterar imagem de perfil:</label>
                    <label class="upload-btn" for="nova_imagem">Escolher imagem</label>
                    <input type="file" name="nova_imagem" id="nova_imagem" accept="image/*" required>
                    <button type="submit" class="submit-btn">Enviar</button>
                </form>
            </div>

            <div class="info">
                <h3>Bem-vindo, <?= htmlspecialchars($name) ?>!</h3>
                <p>ID do usuário: <?= $usuario_id ?></p>
            </div>
        </div>

        <div class="right">
            <div class="filter-tab" onclick="toggleSidePanel()">Filtros</div>

            <div class="side-panel" id="sidePanel">
                <button class="close-btn" onclick="toggleSidePanel()">✖</button>
                <div class="filters">
                    <!-- Filtros de tipo e geração -->
                    <form method="get">
                        <label>Tipo:
                            <select name="type">
                                <option value="">Todos</option>
                                <option value="normal">Normal</option>
                                <option value="fire">Fogo</option>
                                <option value="water">Água</option>
                                <option value="grass">Grama</option>
                                <option value="bug">Inseto</option>
                                <option value="poison">Veneno</option>
                                <option value="rock">Pedra</option>
                                <option value="ground">Terra</option>
                                <option value="flying">Voador</option>
                                <option value="electric">Elétrico</option>
                                <option value="fighting">Lutador</option>
                                <option value="dark">Sombrio</option>
                                <option value="ghost">Fantasma</option>
                                <option value="fairy">Fada</option>
                                <option value="psychic">Psíquico</option>
                                <option value="dragon">Dragão</option>
                                <option value="steel">Metal</option>
                                <option value="ice">Gelo</option>
                            </select>
                        </label>

                        <label>Geração:
                            <select name="generation">
                                <option value="">Todas</option>
                                <option value="1">Gen 1</option>
                                <option value="2">Gen 2</option>
                                <option value="3">Gen 3</option>
                                <option value="4">Gen 4</option>
                                <option value="5">Gen 5</option>
                                <option value="6">Gen 6</option>
                                <option value="7">Gen 7</option>
                                <option value="8">Gen 8</option>
                                <option value="9">Gen 9</option>
                            </select>
                        </label>
                        <button type="submit">Filtrar</button>
                    </form>
                </div>
            </div>

            <div id="favoritos" style="display:block">
                <h3>Favoritos:</h3>
                <div class="evolution-line" style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <?php if (empty($favoritos)): ?>
                        <p>Você ainda não favoritou nenhum Pokémon.</p>
                    <?php else: ?>
                        <?php foreach ($favoritos as $poke):
                            $pokeData = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon/" . strtolower($poke)), true);
                            $pokeImage = $pokeData['sprites']['other']['official-artwork']['front_default'];
                            ?>
                            <div class="evolution-item" style="text-align: center;">
                                <img src="<?= $pokeImage ?>" alt="<?= $poke ?>"
                                    style="width:80px; display:block; margin: 0 auto;">
                                <p><?= ucfirst($poke) ?></p>
                                <form method="post" action="unfavorite.php" style="margin-top: 5px;">
                                    <input type="hidden" name="pokemon_nome" value="<?= $poke ?>">
                                    <button type="submit"
                                        onclick="return confirm('Remover <?= ucfirst($poke) ?> dos favoritos?')">❌
                                        Remover</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Troca de fundo
        const bgButton = document.getElementById("changeBgBtn");
        const body = document.body;
        const backgrounds = [
            "image/4840016244_7445a0f092_b.jpg",
            "image/novo_fundo.jpg"
        ];
        let currentBgIndex = localStorage.getItem("bgIndex") ? parseInt(localStorage.getItem("bgIndex")) : 0;
        body.style.backgroundImage = `url('${backgrounds[currentBgIndex]}')`;
        bgButton.addEventListener("click", () => {
            currentBgIndex = (currentBgIndex + 1) % backgrounds.length;
            body.style.backgroundImage = `url('${backgrounds[currentBgIndex]}')`;
            localStorage.setItem("bgIndex", currentBgIndex);
        });

        // Painel de filtros
        function toggleSidePanel() {
            const panel = document.getElementById("sidePanel");
            panel.classList.toggle("open");
        }

        // Controle da animação
        const pokedex = document.querySelector('.pokedex');
        const toggleAnimBtn = document.getElementById('toggleAnimation');
        let animState = localStorage.getItem('pokedexAnimation') || 'running';
        pokedex.style.animationPlayState = animState;

        toggleAnimBtn.addEventListener('click', () => {
            if (pokedex.style.animationPlayState === 'running') {
                pokedex.style.animationPlayState = 'paused';
                localStorage.setItem('pokedexAnimation', 'paused');
            } else {
                pokedex.style.animationPlayState = 'running';
                localStorage.setItem('pokedexAnimation', 'running');
            }
        });
    </script>
</body>

</html>
