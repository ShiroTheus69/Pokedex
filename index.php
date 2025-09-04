<?php
$total = 151;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
if ($id < 1) $id = 1;
if ($id > $total) $id = $total;
 
$url = "https://pokeapi.co/api/v2/pokemon/$id";
$data = json_decode(file_get_contents($url), true);
 
// Nome, tipos, imagem
$name = ucfirst($data['name']);
$types = array_map(fn($t) => ucfirst($t['type']['name']), $data['types']);
$image = $data['sprites']['other']['official-artwork']['front_default'];
$height = $data['height'] / 10;
$weight = $data['weight'] / 10;
 
// Descrição (puxa da species)
$speciesUrl = $data['species']['url'];
$species = json_decode(file_get_contents($speciesUrl), true);
$flavor = "";
foreach ($species['flavor_text_entries'] as $entry) {
    if ($entry['language']['name'] === 'en') { // pode trocar para 'es' se quiser espanhol
        $flavor = $entry['flavor_text'];
        break;
    }
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
  <div class="right"
    <div class="desc-screen">
      <p><?= nl2br($flavor) ?></p>
    </div>
    <div class="buttons">
  <span class="square sound-btn"></span>
  <span class="square sound-btn"></span>
  <span class="rect sound-btn"></span>
  <span class="rect sound-btn"></span>
    </div>
  </div>
</div>
</body>
</html>

<script>
  // Nome do Pokémon atual em minúsculo
  const pokemonName = "<?= strtolower($name) ?>";
  // Cria um elemento de áudio
  const audio = new Audio(`https://play.pokemonshowdown.com/audio/cries/${pokemonName}.ogg`);
  
  // Ao clicar em qualquer botão com classe .sound-btn, toca o som
  document.querySelectorAll('.sound-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      audio.currentTime = 0;  // reinicia
      audio.play();
    });
  });
</script>
 