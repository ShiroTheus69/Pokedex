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

// Caminho padrão
$defaultImage = "image/hilda.jpg";

// Busca imagem personalizada no banco
$sql = "SELECT imagem_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Se existe imagem personalizada e arquivo existe -> usa ela, senão usa padrão
if (!empty($row['imagem_perfil']) && file_exists($row['imagem_perfil'])) {
    // append timestamp pra evitar cache do navegador após upload
    $image = $row['imagem_perfil'] . "?v=" . filemtime($row['imagem_perfil']);
} else {
    $image = $defaultImage;
}

// Buscar Pokémon favoritados (mantive sua lógica)
$favoritos = [];
$sql = "SELECT pokemon_nome FROM favoritos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

while ($r = $result->fetch_assoc()) {
    $favoritos[] = $r['pokemon_nome'];
}
?>
<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title>Pokédex - <?= htmlspecialchars($name) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* estilos mínimos e harmonizados (botões e enquadramento retangular) */
        .screen {
            width: 220px;
            height: auto;
            padding: 8px;
            border-radius: 8px;
            background: #f6f6f8;
            border: 2px solid #dcdcdc;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 8px auto;
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
        }
        .screen img {
            max-width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            border-radius: 4px; /* leve arredondamento, mantém formato retangular */
        }

        .upload-container {
            text-align: center;
            margin-top: 10px;
        }

        /* esconder input file real e estilizar label como botão */
        .file-input {
            display: none;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: transform .12s ease, box-shadow .12s ease;
        }

        .btn-primary {
            background: linear-gradient(180deg,#2a75bb,#1b4f7a);
            color: #fff;
            box-shadow: 0 6px 12px rgba(42,117,187,0.18);
        }
        .btn-primary:hover { transform: translateY(-2px); }

        .btn-accent {
            background: linear-gradient(180deg,#ffcb05,#f2a900);
            color: #08306b;
            box-shadow: 0 6px 12px rgba(255,203,5,0.12);
            margin-left: 8px;
        }
        .btn-accent:hover { transform: translateY(-2px); }

        /* label que funciona como botão para escolher arquivo */
        label.file-label {
            cursor: pointer;
            display: inline-block;
        }

        /* pequena pré-visualização quando escolher (mobile friendly) */
        #preview {
            display: none;
            margin-top: 8px;
            max-width: 220px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        /* manter seu layout existente, só melhorei os controles */
        .info { text-align: center; margin-top: 10px; }
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

            <!-- tela com imagem (mantive posição e estrutura) -->
            <div class="screen">
                <img id="userImage" src="<?= htmlspecialchars($image) ?>" alt="Imagem do usuário">
            </div>

            <!-- FORMULÁRIO DE UPLOAD ABAIXO DA IMAGEM (preservei fluxo: action para upload_imagem.php) -->
            <div class="upload-container">
                <form id="uploadForm" action="upload_imagem.php" method="post" enctype="multipart/form-data">
                    <label for="nova_imagem" class="file-label btn btn-accent">Escolher imagem</label>
                    <input class="file-input" type="file" name="nova_imagem" id="nova_imagem" accept="image/*" onchange="handleFileChange(event)">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                    <img id="preview" alt="Pré-visualização">
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
                    <!-- Filtros de tipo e geração (mantidos) -->
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
        // pré-visualização local antes do upload e update visual (não persiste até o upload)
        function handleFileChange(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview');
            if (!file) {
                preview.style.display = 'none';
                return;
            }
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            preview.onload = () => URL.revokeObjectURL(preview.src);
        }

        // Troca de fundo (mantive seu script)
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
        if (pokedex) pokedex.style.animationPlayState = animState;

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
