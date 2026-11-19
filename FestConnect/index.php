<?php

require_once 'config/database.php';
require_once 'includes/header.php';

$categoria = $_GET['categoria'] ?? '';
$preco_max = $_GET['preco_max'] ?? '';

$sql = "SELECT p.*, u.nome FROM perfis_profissionais p 
        JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $sql .= " AND p.categoria = :categoria";
    $params[':categoria'] = $categoria;
}
$faixa_preco = $_GET['faixa_preco'] ?? '';

$sql = "SELECT p.*, u.nome FROM perfis_profissionais p 
        JOIN usuarios u ON p.usuario_id = u.id WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $sql .= " AND p.categoria = :categoria";
    $params[':categoria'] = $categoria;
}

if (!empty($faixa_preco)) {
    $partes = explode('-', $faixa_preco);
    if (count($partes) === 2) {
        $sql .= " AND p.faixa_preco BETWEEN :preco_min AND :preco_max";
        $params[':preco_min'] = (float)$partes[0];
        $params[':preco_max'] = (float)$partes[1];
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$profissionais = $stmt->fetchAll();
?>


<section class="hero-search">
    <h2>Encontre os melhores profissionais para o seu evento</h2>
    <form class="search-form" method="GET" action="index.php">
        <select name="categoria">
            <option value="">Todas as Categorias</option>
            <option value="DJ" <?php if($categoria=='DJ') echo 'selected'; ?>>DJ</option>
            <option value="Banda" <?php if($categoria=='Banda') echo 'selected'; ?>>Banda</option>
            <option value="Musico Solo" <?php if($categoria=='Musico Solo') echo 'selected'; ?>>Músico Solo</option>
            <option value="Duo" <?php if($categoria=='Duo') echo 'selected'; ?>>Duo</option>
            <option value="Fotografo" <?php if($categoria=='Fotografo') echo 'selected'; ?>>Fotógrafo</option>
            <option value="Tecnico de Som" <?php if($categoria=='Tecnico de Som') echo 'selected'; ?>>Técnico de Som</option>
            <option value="Iluminacao" <?php if($categoria=='Iluminacao') echo 'selected'; ?>>Iluminação</option>
        </select>
        <button type="submit" class="btn">Pesquisar</button>
    </form>
</section>

<div class="main-content">
    <aside class="filters">
        <h3>Filtros Avançados</h3>
        <form method="GET" action="index.php">
            <input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>">
<div class="filter-group">
    <label for="faixa_preco">Faixa de Preço (R$)</label>
    <select name="faixa_preco" id="faixa_preco" style="width: 100%; padding: 8px; margin-top: 5px;">
        <option value="">Todas as faixas</option>
        <option value="100-599" <?php if(($_GET['faixa_preco']??'')=='100-599') echo 'selected'; ?>>R$ 100 a R$ 599</option>
        <option value="600-1099" <?php if(($_GET['faixa_preco']??'')=='600-1099') echo 'selected'; ?>>R$ 600 a R$ 1099</option>
        <option value="1100-1599" <?php if(($_GET['faixa_preco']??'')=='1100-1599') echo 'selected'; ?>>R$ 1100 a R$ 1599</option>
        <option value="1600-2099" <?php if(($_GET['faixa_preco']??'')=='1600-2099') echo 'selected'; ?>>R$ 1600 a R$ 2099</option>
        <option value="2100-2599" <?php if(($_GET['faixa_preco']??'')=='2100-2599') echo 'selected'; ?>>R$ 2100 a R$ 2599</option>
        <option value="2600-3099" <?php if(($_GET['faixa_preco']??'')=='2600-3099') echo 'selected'; ?>>R$ 2600 a R$ 3099</option>
        <option value="3100-3599" <?php if(($_GET['faixa_preco']??'')=='3100-3599') echo 'selected'; ?>>R$ 3100 a R$ 3599</option>
        <option value="3600-4099" <?php if(($_GET['faixa_preco']??'')=='3600-4099') echo 'selected'; ?>>R$ 3600 a R$ 4099</option>
        <option value="4100-4599" <?php if(($_GET['faixa_preco']??'')=='4100-4599') echo 'selected'; ?>>R$ 4100 a R$ 4599</option>
        <option value="4600-5099" <?php if(($_GET['faixa_preco']??'')=='4600-5099') echo 'selected'; ?>>R$ 4600 a R$ 5099</option>
        <option value="5100-5599" <?php if(($_GET['faixa_preco']??'')=='5100-5599') echo 'selected'; ?>>R$ 5100 a R$ 5599</option>
        <option value="5600-6099" <?php if(($_GET['faixa_preco']??'')=='5600-6099') echo 'selected'; ?>>R$ 5600 a R$ 6099</option>
        <option value="6100-6599" <?php if(($_GET['faixa_preco']??'')=='6100-6599') echo 'selected'; ?>>R$ 6100 a R$ 6599</option>
        <option value="6600-7099" <?php if(($_GET['faixa_preco']??'')=='6600-7099') echo 'selected'; ?>>R$ 6600 a R$ 7099</option>
        <option value="7100-7599" <?php if(($_GET['faixa_preco']??'')=='7100-7599') echo 'selected'; ?>>R$ 7100 a R$ 7599</option>
        <option value="7100-8099" <?php if(($_GET['faixa_preco']??'')=='7100-8099') echo 'selected'; ?>>R$ 7100 a R$ 8099</option>
        <option value="7100-8599" <?php if(($_GET['faixa_preco']??'')=='7100-8599') echo 'selected'; ?>>R$ 7100 a R$ 8599</option>
        <option value="7100-9099" <?php if(($_GET['faixa_preco']??'')=='7100-9099') echo 'selected'; ?>>R$ 7100 a R$ 9099</option>
        <option value="7100-9599" <?php if(($_GET['faixa_preco']??'')=='7100-9599') echo 'selected'; ?>>R$ 7100 a R$ 9599</option>
        <option value="7100-10099" <?php if(($_GET['faixa_preco']??'')=='7100-10099') echo 'selected'; ?>>R$ 7100 a R$ 10099</option>
    </select>
</div>
            <button type="submit" class="btn">Aplicar Filtros</button>
        </form>
    </aside>

    <main class="cards-grid">
        <?php if (count($profissionais) > 0): ?>
            <?php foreach ($profissionais as $prof): ?>
                <article class="card">
                    <h3><?php echo htmlspecialchars($prof['nome']); ?></h3>
                    <p><strong>Categoria:</strong> <?php echo htmlspecialchars($prof['categoria']); ?></p>
                    <p><strong>Local:</strong> <?php echo htmlspecialchars($prof['localizacao']); ?></p>
                    <div class="card-price">A partir de R$ <?php echo number_format($prof['faixa_preco'], 2, ',', '.'); ?></div>
                    <a href="perfil.php?id=<?php echo $prof['id']; ?>" class="btn">Ver Perfil Completo</a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum profissional encontrado com os critérios selecionados.</p>
        <?php endif; ?>
    </main>
</div>

</div>
</body>
</html>