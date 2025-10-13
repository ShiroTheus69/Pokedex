<?php
session_start();
include("db.php");

// Descobre o total de Pokémon disponíveis
$countData = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon?limit=1"), true);
$total = $countData['count'];

// Parâmetros da URL
$typeFilter = $_GET['type'] ?? '';
$generationFilter = $_GET['generation'] ?? '';
$currentIndex = isset($_GET['index']) ? (int) $_GET['index'] : 0;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Inicializa listas
$pokemonList = [];
$speciesList = [];
$filteredList = [];
$name = null;

// Pesquisa Pokemon
$searchInput = $_GET['search'] ?? '';
if (!empty($searchInput)) {
  $nameInput = strtolower(trim($searchInput));

  // Detecta se é número ou nome
  if (is_numeric($nameInput)) {
    $id = (int) $nameInput;
    $url = "https://pokeapi.co/api/v2/pokemon/$id";
  } else {
    // Tenta buscar pelo nome
    $url = "https://pokeapi.co/api/v2/pokemon/$nameInput";
  }

  // Verifica se o Pokémon existe
  $response = @file_get_contents($url);
  if ($response === false) {
    // Se não existir, volta pro primeiro Pokémon
    $url = "https://pokeapi.co/api/v2/pokemon/1";
  } else {
    $data = json_decode($response, true);
    $id = $data['id'];
  }
}

// Filtro por tipo
if (!empty($typeFilter)) {
  $typeUrl = "https://pokeapi.co/api/v2/type/$typeFilter";
  $typeData = json_decode(file_get_contents($typeUrl), true);
  $pokemonList = array_map(fn($p) => $p['pokemon']['name'], $typeData['pokemon']);
}

// Filtro por geração
if (!empty($generationFilter)) {
  $genUrl = "https://pokeapi.co/api/v2/generation/$generationFilter";
  $genData = json_decode(file_get_contents($genUrl), true);
  $speciesList = array_map(fn($s) => $s['name'], $genData['pokemon_species']);
}

// Aplica interseção se ambos os filtros estiverem ativos
if (!empty($pokemonList) && !empty($speciesList)) {
  $filteredList = array_values(array_intersect($pokemonList, $speciesList));
} elseif (!empty($pokemonList)) {
  $filteredList = $pokemonList;
} elseif (!empty($speciesList)) {
  $filteredList = $speciesList;
}

// Define o nome do Pokémon atual
if (!empty($filteredList)) {
  if ($currentIndex < 0)
    $currentIndex = 0;
  if ($currentIndex >= count($filteredList))
    $currentIndex = count($filteredList) - 1;
  $name = $filteredList[$currentIndex];
  $url = "https://pokeapi.co/api/v2/pokemon/$name";
} else {
  if ($id < 1)
    $id = 1;
  if ($id > $total)
    $id = $total;
  $url = "https://pokeapi.co/api/v2/pokemon/$id";
}

// Dados principais do Pokémon
$data = json_decode(file_get_contents($url), true);
$name = ucfirst($data['name']);
$types = array_map(fn($t) => ucfirst($t['type']['name']), $data['types']);
$image = $data['sprites']['other']['official-artwork']['front_default'];
$height = $data['height'] / 10;
$weight = $data['weight'] / 10;

// Número real do Pokémon
$speciesUrl = $data['species']['url'];
preg_match('/\/pokemon-species\/(\d+)\//', $speciesUrl, $matches);
$pokemonNumber = $matches[1] ?? $id;

// Descrição
$species = json_decode(file_get_contents($speciesUrl), true);
$flavor = "";
foreach ($species['flavor_text_entries'] as $entry) {
  if ($entry['language']['name'] === 'en') {
    $flavor = $entry['flavor_text'];
    break;
  }
}

// Evoluções
$evolutionUrl = $species['evolution_chain']['url'];
$evolutionData = json_decode(file_get_contents($evolutionUrl), true);
function getEvolutions($chain)
{
  $evolutions = [ucfirst($chain['species']['name'])];
  foreach ($chain['evolves_to'] as $evo) {
    $evolutions = array_merge($evolutions, getEvolutions($evo));
  }
  return $evolutions;
}
$evolutions = getEvolutions($evolutionData['chain']);

if (isset($_SESSION['usuario_nome'])) {
  // Usuário está logado
  echo '<a href="logout.php"><button id="logout">Deslogar</button></a>';
} else {
  // Usuário não está logado
  echo '<a href="login.php"><button id="login">Logar</button></a>';
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
  <a href="perfil.php"><button id="perfil">Ir para o Perfil</button></a>


  <div class="pokedex">
    <!-- Parte esquerda -->
    <div class="left">
      <div class="lights">
        <span class="light blue main-button"></span>
        <div class="side-buttons">
          <span class="light red"></span>
          <span class="light yellow"></span>
          <span class="light green"></span>
        </div>
      </div>

      <form method="get" class="search-bar">
        <input type="text" name="search" placeholder="Buscar por nome ou número..." required>
        <button type="submit">🔍</button>
      </form>

      <div class="screen">
        <img src="<?= $image ?>" alt="<?= $name ?>">
      </div>
      <div class="info">
        <h2>#<?= str_pad($pokemonNumber, 3, '0', STR_PAD_LEFT) ?> - <?= $name ?></h2>
        <p><strong>Tipo:</strong> <?= implode(', ', $types) ?></p>
        <p><strong>Altura:</strong> <?= $height ?> m</p>
        <p><strong>Peso:</strong> <?= $weight ?> kg</p>
      </div>
      <div class="controls">
        <?php if (!empty($filteredList)): ?>
          <?php if ($currentIndex > 0): ?>
            <a href="?index=<?= $currentIndex - 1 ?>&type=<?= urlencode($typeFilter) ?>&generation=<?= urlencode($generationFilter) ?>"
              class="btn">◀</a>
          <?php endif; ?>
          <?php if ($currentIndex < count($filteredList) - 1): ?>
            <a href="?index=<?= $currentIndex + 1 ?>&type=<?= urlencode($typeFilter) ?>&generation=<?= urlencode($generationFilter) ?>"
              class="btn">▶</a>
          <?php endif; ?>
        <?php else: ?>
          <?php if ($id > 1): ?>
            <a href="?id=<?= $id - 1 ?>" class="btn">◀</a>
          <?php endif; ?>
          <?php if ($id < $total): ?>
            <a href="?id=<?= $id + 1 ?>" class="btn">▶</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="right">

      <div class="filter-tab" onclick="toggleSidePanel()">Filtros</div>

      <!-- Painel de filtros oculto -->
      <div class="side-panel" id="sidePanel">
        <button class="close-btn" onclick="toggleSidePanel()">✖</button>
        <div class="filters">
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

      <div class="desc-screen">
        <p><?= nl2br($flavor) ?></p>
      </div>
      <div class="buttons">
        <button class="sound-btn">Som</button>
        <button onclick="location.href='favorite.php?id=<?= $pokemonNumber ?>&name=<?= $name ?>'">⭐ Favoritar</button>
      </div>

      <div id="evolutions" style="display:block">
        <h3>Evoluções:</h3>
        <div class="evolution-line">
          <?php foreach ($evolutions as $evo):
            $evoData = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon/" . strtolower($evo)), true);
            $evoImage = $evoData['sprites']['other']['official-artwork']['front_default'];
            ?>
            <div class="evolution-item">
              <img src="<?= $evoImage ?>" alt="<?= $evo ?>" style="width:80px">
              <p><?= $evo ?></p>
            </div>
          <?php endforeach; ?>
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

    // Som do Pokémon
    const pokemonName = "<?= strtolower($data['name']) ?>";
    const audio = new Audio(`https://play.pokemonshowdown.com/audio/cries/${pokemonName}.ogg`);
    document.querySelectorAll('.sound-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        audio.currentTime = 0;
        audio.play();
      });
    });

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