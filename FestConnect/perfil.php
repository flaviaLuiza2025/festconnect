<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

$id = $_GET['id'] ?? 0;

// Buscar dados do profissional
$stmt = $pdo->prepare("SELECT p.*, u.nome FROM perfis_profissionais p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = :id");
$stmt->execute([':id' => $id]);
$prof = $stmt->fetch();

if (!$prof) { 
    die("Profissional não encontrado."); 
}

// 1. Processar Solicitação de Orçamento (RF10)
$msg = '';
$msg_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_orcamento'])) {
    if (isset($_SESSION['usuario_id'])) {
        $stmt = $pdo->prepare("INSERT INTO orcamencos (cliente_id, profissional_id, data_evento, tipo_evento, mensagem) VALUES (:cli, :pro, :data_e, :tipo, :msg)");
        $stmt->execute([
            ':cli' => $_SESSION['usuario_id'],
            ':pro' => $id,
            ':data_e' => $_POST['data_evento'],
            ':tipo' => $_POST['tipo_evento'],
            ':msg' => $_POST['mensagem']
        ]);
        $msg = "Solicitação de orçamento enviada com sucesso!";
        $msg_tipo = "sucesso";
    } else {
        $msg = "Você precisa estar logado como cliente para solicitar um orçamento.";
        $msg_tipo = "erro";
    }
}

// 2. Processar Nova Avaliação (Comentário e Nota)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_avaliacao'])) {
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_tipo'] === 'cliente') {
        $nota = (int)$_POST['nota'];
        $comentario = trim($_POST['comentario']);

        if ($nota >= 1 && $nota <= 5) {
            $stmt = $pdo->prepare("INSERT INTO avaliacoes (profissional_id, cliente_id, nota, comentario) VALUES (:pid, :cid, :nota, :comentario)");
            $stmt->execute([
                ':pid' => $id,
                ':cid' => $_SESSION['usuario_id'],
                ':nota' => $nota,
                ':comentario' => $comentario
            ]);
            $msg = "Avaliação enviada com sucesso!";
            $msg_tipo = "sucesso";
        }
    } else {
        $msg = "Apenas clientes logados podem enviar avaliações.";
        $msg_tipo = "erro";
    }
}

// Buscar Galeria
$stmt = $pdo->prepare("SELECT * FROM galeria_profissionais WHERE profissional_id = :pid ORDER BY id DESC");
$stmt->execute([':pid' => $prof['id']]);
$galeria = $stmt->fetchAll();

// Buscar Avaliações com Nome do Cliente
$stmt = $pdo->prepare("
    SELECT a.*, u.nome AS cliente_nome 
    FROM avaliacoes a 
    JOIN usuarios u ON a.cliente_id = u.id 
    WHERE a.profissional_id = :pid 
    ORDER BY a.id DESC
");
$stmt->execute([':pid' => $id]);
$avaliacoes = $stmt->fetchAll();

// Média das Notas
$stmt = $pdo->prepare("SELECT AVG(nota) as media, COUNT(*) as total FROM avaliacoes WHERE profissional_id = :pid");
$stmt->execute([':pid' => $id]);
$stats = $stmt->fetch();
$media_nota = number_format($stats['media'] ?? 0, 1, ',', '.');
$total_avaliacoes = $stats['total'];

require_once 'includes/header.php';
?>

<h2>
    <?php echo htmlspecialchars($prof['nome']); ?> - <?php echo htmlspecialchars($prof['categoria']); ?>
    <span style="font-size: 1.1rem; color: #f39c12; margin-left: 10px;">
        ★ <?php echo $media_nota; ?>/5 (<?php echo $total_avaliacoes; ?> avaliações)
    </span>
</h2>
<p><strong>Localização:</strong> <?php echo htmlspecialchars($prof['localizacao']); ?></p>
<p><strong>Descrição:</strong> <?php echo nl2br(htmlspecialchars($prof['descricao'])); ?></p>
<p><strong>Preço estimado:</strong> R$ <?php echo number_format($prof['faixa_preco'], 2, ',', '.'); ?></p>

<!-- Contato WhatsApp -->
<?php if (!empty($prof['contato_whatsapp'])): ?>
    <?php 
        $whatsapp_limpo = preg_replace('/[^0-9]/', '', $prof['contato_whatsapp']); 
        if (strlen($whatsapp_limpo) >= 10 && strlen($whatsapp_limpo) <= 11) {
            $whatsapp_limpo = '55' . $whatsapp_limpo;
        }
    ?>
    <p>
        <a href="https://wa.me/<?php echo $whatsapp_limpo; ?>" target="_blank" class="btn" style="background:#25D366;">
            Contato via WhatsApp
        </a>
    </p>
<?php endif; ?>

<hr style="margin:20px 0;">

<!-- GALERIA DE FOTOS COM AMPLIÇÃO AO CLICAR -->
<h3>Galeria de Trabalhos</h3>
<?php if (count($galeria) > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
        <?php foreach ($galeria as $foto): ?>
            <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; cursor: pointer;" onclick="ampliarImagem('<?php echo htmlspecialchars($foto['caminho_imagem']); ?>', '<?php echo htmlspecialchars($foto['titulo']); ?>')">
                <img src="<?php echo htmlspecialchars($foto['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($foto['titulo']); ?>" style="width: 100%; height: 160px; object-fit: cover; display: block; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                <?php if ($foto['titulo']): ?>
                    <p style="padding: 8px; font-size: 0.9rem; margin: 0; color: #555; text-align: center;"><?php echo htmlspecialchars($foto['titulo']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="margin-bottom: 30px; color: #777;">Este profissional ainda não adicionou fotos ao seu portfólio.</p>
<?php endif; ?>

<!-- MODAL PARA AMPLIAR A IMAGEM (COLOCAR NO FINAL DO PERFIL.PHP) -->
<div id="modalImagem" onclick="fecharModal()" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); align-items: center; justify-content: center; flex-direction: column;">
    <span onclick="fecharModal()" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: #fff; cursor: pointer; font-weight: bold;">&times;</span>
    <img id="imagemExpandida" style="max-width: 90%; max-height: 80vh; border-radius: 8px; box-shadow: 0px 4px 15px rgba(0,0,0,0.5);">
    <p id="legendaImagem" style="color: #fff; font-size: 1.1rem; margin-top: 15px; text-align: center;"></p>
</div>

<script>
function ampliarImagem(src, titulo) {
    var modal = document.getElementById("modalImagem");
    var img = document.getElementById("imagemExpandida");
    var legenda = document.getElementById("legendaImagem");

    modal.style.display = "flex";
    img.src = src;
    legenda.innerText = titulo ? titulo : '';
}

function fecharModal() {
    document.getElementById("modalImagem").style.display = "none";
}
</script>

<!-- FORMULÁRIO DE ORÇAMENTO -->
<div class="form-box" style="margin: 0 0 30px 0;">
    <h3>Solicitar Orçamento</h3>
    <?php if ($msg): ?>
        <p style="color: <?php echo ($msg_tipo === 'sucesso') ? 'green' : 'red'; ?>; font-weight: bold; margin-bottom: 15px;">
            <?php echo $msg; ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="solicitar_orcamento" value="1">
        <div class="form-group">
            <label>Data do Evento</label>
            <input type="date" name="data_evento" required>
        </div>
        <div class="form-group">
            <label>Tipo de Evento</label>
            <input type="text" name="tipo_evento" placeholder="Ex: Casamento, Aniversário" required>
        </div>
        <div class="form-group">
            <label>Detalhes/Mensagem</label>
            <textarea name="mensagem" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn">Enviar Pedido</button>
    </form>
</div>

<hr style="margin:20px 0;">

<!-- SESSÃO DE AVALIAÇÕES E COMENTÁRIOS -->
<h3>Avaliações dos Clientes</h3>

<?php if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_tipo'] === 'cliente'): ?>
    <div class="form-box" style="margin: 15px 0 30px 0;">
        <h4>Deixe sua avaliação sobre o profissional</h4>
        <form method="POST">
            <input type="hidden" name="enviar_avaliacao" value="1">
            <div class="form-group">
                <label>Sua Nota (1 a 5 Estrelas)</label>
                <select name="nota" required>
                    <option value="5">★★★★★ (5/5 - Excelente)</option>
                    <option value="4">★★★★☆ (4/5 - Muito Bom)</option>
                    <option value="3">★★★☆☆ (3/5 - Bom)</option>
                    <option value="2">★★☆☆☆ (2/5 - Regular)</option>
                    <option value="1">★☆☆☆☆ (1/5 - Ruim)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Seu Comentário / Depoimento</label>
                <textarea name="comentario" rows="3" placeholder="Conte como foi o atendimento, pontualidade e qualidade do serviço..." required></textarea>
            </div>
            <button type="submit" class="btn" style="background: #3498db;">Publicar Avaliação</button>
        </form>
    </div>
<?php elseif (!isset($_SESSION['usuario_id'])): ?>
    <p><a href="login.php">Faça login como cliente</a> para deixar uma avaliação.</p>
<?php endif; ?>

<!-- LISTAGEM DOS COMENTÁRIOS -->
<div style="display: flex; flex-direction: column; gap: 15px; margin-top: 15px;">
    <?php if (count($avaliacoes) > 0): ?>
        <?php foreach ($avaliacoes as $av): ?>
            <div style="background: #fff; border: 1px solid #e0e0e0; padding: 15px; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong><?php echo htmlspecialchars($av['cliente_nome']); ?></strong>
                    <span style="color: #f39c12; font-weight: bold;">
                        <?php echo str_repeat('★', $av['nota']) . str_repeat('☆', 5 - $av['nota']); ?>
                    </span>
                </div>
                <p style="margin-top: 8px; color: #444;"><?php echo nl2br(htmlspecialchars($av['comentario'])); ?></p>
                <?php 
    // Garante que pega a data correta sem gerar Warning
                    $data = $av['data_registro'] ?? $av['data_avaliacao'] ?? $av['created_at'] ?? null; 
                ?>
                <small style="color: #888;">
                    <?php echo $data ? date('d/m/Y H:i', strtotime($data)) : ''; ?>
                </small>
                
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma avaliação cadastrada ainda.</p>
    <?php endif; ?>
</div>

</div>
</body>
</html>