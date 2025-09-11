<?php
$countData = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon?limit=1"), true);
$total = $countData['count']; // Ex: 1025+

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
if ($id < 1) $id = 1;
if ($id > $total) $id = $total;

// Filtros
$typeFilter = $_GET['type'] ?? '';
$generationFilter = $_GET['generation'] ?? '';

// Dados principais
$url = "https://pokeapi.co/api/v2/pokemon/$id";
$data = json_decode(file_get_contents($url), true);

$name = ucfirst($data['name']);
$types = array_map(fn($t) => ucfirst($t['type']['name']), $data['types']);
$image = $data['sprites']['other']['official-artwork']['front_default'];
$height = $data['height'] / 10;
$weight = $data['weight'] / 10;

// Descrição
$speciesUrl = $data['species']['url'];
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
$evolutions = [];
function getEvolutions($chain) {
    $evolutions = [ucfirst($chain['species']['name'])];
    foreach ($chain['evolves_to'] as $evo) {
        $evolutions = array_merge($evolutions, getEvolutions($evo));
    }
    return $evolutions;
}
$evolutions = getEvolutions($evolutionData['chain']);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Pokédex - <?= $name ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="pokedex">
  <!-- Parte esquerda -->
  <div class="left">
    <div class="lights">
      <span class="light blue"></span>
      <span class="light red"></span>
      <span class="light yellow"></span>
      <span class="light green"></span>
    </div>
    <div class="screen">
      <img src="<?= $image ?>" alt="<?= $name ?>">
    </div>
    <div class="info">
      <h2>#<?= str_pad($id,3,'0',STR_PAD_LEFT) ?> - <?= $name ?></h2>
      <p><strong>Tipo:</strong> <?= implode(', ', $types) ?></p>
      <p><strong>Altura:</strong> <?= $height ?> m</p>
      <p><strong>Peso:</strong> <?= $weight ?> kg</p>
    </div>
    <div class="controls">
      <?php if ($id > 1): ?>
        <a href="?id=<?= $id-1 ?>" class="btn">◀</a>
      <?php endif; ?>
      <?php if ($id < $total): ?>
        <a href="?id=<?= $id+1 ?>" class="btn">▶</a>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Parte direita -->
  <div class="right">
    <div class="filters">
      <form method="get">
        <label>Tipo:
          <select name="type">
            <option value="">Todos</option>
            <option value="fire">Fogo</option>
            <option value="water">Água</option>
            <option value="grass">Grama</option>
          </select>
            <!-- Adicione mais tipos -->
        <label>Geração:
          <select name="generation">
            <option value="">Todas</option>
            <option value="1">Gen 1</option>
            <option value="2">Gen 2</option>
              <!-- Adicione mais gerações -->
          </select>
          </label>
        </label>
        <button type="submit">Filtrar</button>
      </form>
    </div>
    <div class="desc-screen">
      <p><?= nl2br($flavor) ?></p>
    </div>
    <div class="buttons">
      <button onclick="document.getElementById('cry').play()">🔊 Som</button>
      <audio id="cry" src="sounds/<?= str_pad($id,3,'0',STR_PAD_LEFT) ?>.mp3"></audio>
      
      <button onclick="document.getElementById('evolutions').style.display='block'">🔁 Evoluções</button>
    </div>
    <div id="evolutions" style="display:none">
      <h3>Evoluções:</h3>
      <ul>
        <?php foreach ($evolutions as $evo): ?>
          <li><?= $evo ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</body>
</html>
