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
$image = "image/hilda.jpg";

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
    <title>Pokédex - <?= $name ?></title>
    <link rel="stylesheet" href="style.css">
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

            <div class="screen">
                <img src="<?= $image ?>" alt="Imagem do usuário" style="max-width: 100%; height: auto;">
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
        body.style.backgroundSize = "cover";
        body.style.backgroundRepeat = "no-repeat";
        body.style.backgroundPosition = "center center";
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

        window.addEventListener('DOMContentLoaded', () => {
            const savedState = localStorage.getItem('pokedexAnimation');
            if (savedState) pokedex.style.animationPlayState = savedState;
        });
    </script>
</body>

</html>