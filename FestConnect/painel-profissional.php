<?php
// RF03, RF04, RF11, RF12 - Gestão do Perfil, Galeria e Resposta a Orçamentos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'profissional') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem_status = '';

// 1. Atualizar ou Criar Perfil (RF03, RF04)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_perfil'])) {
    $categoria   = $_POST['categoria'];
    $descricao   = $_POST['descricao'];
    $localizacao = $_POST['localizacao'];
    $preco       = $_POST['faixa_preco'];
    $whatsapp    = $_POST['contato_whatsapp'];

    $stmt = $pdo->prepare("SELECT id FROM perfis_profissionais WHERE usuario_id = :uid");
    $stmt->execute([':uid' => $usuario_id]);
    $existe = $stmt->fetch();

    if ($existe) {
        $stmt = $pdo->prepare("UPDATE perfis_profissionais SET categoria = :cat, descricao = :desc, localizacao = :loc, faixa_preco = :preco, contato_whatsapp = :wsp WHERE usuario_id = :uid");
    } else {
        $stmt = $pdo->prepare("INSERT INTO perfis_profissionais (usuario_id, categoria, descricao, localizacao, faixa_preco, contato_whatsapp) VALUES (:uid, :cat, :desc, :loc, :preco, :wsp)");
    }
    $stmt->execute([':uid' => $usuario_id, ':cat' => $categoria, ':desc' => $descricao, ':loc' => $localizacao, ':preco' => $preco, ':wsp' => $whatsapp]);
    $mensagem_status = "Perfil atualizado com sucesso!";
}

// Buscar dados do perfil profissional
$stmt = $pdo->prepare("SELECT * FROM perfis_profissionais WHERE usuario_id = :uid");
$stmt->execute([':uid' => $usuario_id]);
$perfil = $stmt->fetch();

// 2. Upload de Múltiplas Fotos para a Galeria
// 2. Upload de Múltiplas Fotos para a Galeria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_foto'])) {
    if (!$perfil) {
        $mensagem_status = "ERRO: Deve preencher e clicar em 'Salvar Perfil' acima antes de enviar fotos.";
    } elseif (isset($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $total_arquivos = count($_FILES['fotos']['name']);
        $enviadas = 0;
        $erros = [];

        $pasta_destino = 'uploads/';
        if (!is_dir($pasta_destino)) {
            @mkdir($pasta_destino, 0777, true);
        }

        for ($i = 0; $i < $total_arquivos; $i++) {
            // Ignora campos vazios
            if (empty($_FILES['fotos']['name'][$i])) {
                continue;
            }

            $erro_code = $_FILES['fotos']['error'][$i];

            if ($erro_code === UPLOAD_ERR_OK) {
                $extensao = strtolower(pathinfo($_FILES['fotos']['name'][$i], PATHINFO_EXTENSION));

                if (in_array($extensao, $extensoes_permitidas)) {
                    $novo_nome = md5(uniqid(rand(), true)) . '.' . $extensao;
                    $destino = $pasta_destino . $novo_nome;

                    if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $destino)) {
                        $titulo = trim($_POST['titulo'] ?? '');
                        $stmt = $pdo->prepare("INSERT INTO galeria_profissionais (profissional_id, caminho_imagem, titulo) VALUES (:pid, :img, :titulo)");
                        $stmt->execute([
                            ':pid' => $perfil['id'],
                            ':img' => $destino,
                            ':titulo' => $titulo
                        ]);
                        $enviadas++;
                    } else {
                        $erros[] = "Erro ao mover: " . htmlspecialchars($_FILES['fotos']['name'][$i]);
                    }
                } else {
                    $erros[] = "Formato inválido: " . htmlspecialchars($_FILES['fotos']['name'][$i]);
                }
            } else {
                $erros[] = "Erro no arquivo " . htmlspecialchars($_FILES['fotos']['name'][$i]) . " (Código PHP: $erro_code)";
            }
        }

        if ($enviadas > 0) {
            $mensagem_status = "$enviadas foto(s) enviada(s) para a galeria com sucesso!";
            if (!empty($erros)) {
                $mensagem_status .= " Algumas fotos falharam: " . implode(" | ", $erros);
            }
        } else {
            $mensagem_status = "Nenhuma foto foi salva. " . implode(" | ", $erros);
        }
    } else {
        $mensagem_status = "Nenhum arquivo foi recebido ou o tamanho total das fotos ultrapassou o limite do servidor.";
    }
}

// 3. Exclusão de Foto da Galeria
if (isset($_GET['deletar_foto']) && $perfil) {
    $foto_id = (int)$_GET['deletar_foto'];
    
    $stmt = $pdo->prepare("SELECT caminho_imagem FROM galeria_profissionais WHERE id = :id AND profissional_id = :pid");
    $stmt->execute([':id' => $foto_id, ':pid' => $perfil['id']]);
    $foto = $stmt->fetch();

    if ($foto) {
        if (file_exists($foto['caminho_imagem'])) {
            unlink($foto['caminho_imagem']);
        }
        $stmt = $pdo->prepare("DELETE FROM galeria_profissionais WHERE id = :id");
        $stmt->execute([':id' => $foto_id]);
        $mensagem_status = "Foto removida com sucesso!";
    }
}

// 4. Responder Solicitação de Orçamento (RF12)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_orcamento'])) {
    $orcamento_id          = $_POST['orcamento_id'];
    $novo_status           = $_POST['status'];
    $resposta_profissional = trim($_POST['resposta_profissional']);
    $valor_proposto        = !empty($_POST['valor_proposto']) ? $_POST['valor_proposto'] : NULL;

    $stmt = $pdo->prepare("UPDATE orcamencos SET status = :status, resposta_profissional = :resp, valor_proposto = :val WHERE id = :id");
    $stmt->execute([
        ':status' => $novo_status,
        ':resp'   => $resposta_profissional,
        ':val'    => $valor_proposto,
        ':id'     => $orcamento_id
    ]);
    $mensagem_status = "Resposta ao orçamento enviada com sucesso!";
}

// Buscar fotos da galeria do profissional
$galeria = [];
if ($perfil) {
    $stmt = $pdo->prepare("SELECT * FROM galeria_profissionais WHERE profissional_id = :pid ORDER BY id DESC");
    $stmt->execute([':pid' => $perfil['id']]);
    $galeria = $stmt->fetchAll();
}

// Buscar orçamentos recebidos (RF11)
$orcamencos = [];
if ($perfil) {
    $stmt = $pdo->prepare("SELECT o.*, u.nome as cliente_nome, u.email as cliente_email FROM orcamencos o JOIN usuarios u ON o.cliente_id = u.id WHERE o.profissional_id = :pid ORDER BY o.id DESC");
    $stmt->execute([':pid' => $perfil['id']]);
    $orcamencos = $stmt->fetchAll();
}

require_once 'includes/header.php';
?>

<h2>Painel do Profissional</h2>

<?php if ($mensagem_status): ?>
    <div style="background: <?php echo (strpos($mensagem_status, 'sucesso') !== false) ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo (strpos($mensagem_status, 'sucesso') !== false) ? '#155724' : '#721c24'; ?>; padding: 12px; border-radius: 6px; margin: 15px 0; font-weight: bold;">
        <?php echo $mensagem_status; ?>
    </div>
<?php endif; ?>

<!-- Form de Gestão de Perfil -->
<div class="form-box" style="margin: 20px 0; max-width: 100%;">
    <h3>Gerenciar Meu Perfil</h3>
    <form method="POST">
        <input type="hidden" name="salvar_perfil" value="1">
        <div class="form-group">
            <label>Categoria</label>
            <select name="categoria">
                <option value="DJ" <?php if(($perfil['categoria']??'')=='DJ') echo 'selected'; ?>>DJ</option>
                <option value="Banda" <?php if(($perfil['categoria']??'')=='Banda') echo 'selected'; ?>>Banda</option>
                <option value="Musico Solo" <?php if(($perfil['categoria']??'')=='Musico Solo') echo 'selected'; ?>>Músico Solo</option>
                <option value="Duo" <?php if(($perfil['categoria']??'')=='Duo') echo 'selected'; ?>>Duo</option>
                <option value="Fotografo" <?php if(($perfil['categoria']??'')=='Fotografo') echo 'selected'; ?>>Fotógrafo</option>
                <option value="Tecnico de Som" <?php if(($perfil['categoria']??'')=='Tecnico de Som') echo 'selected'; ?>>Técnico de Som</option>
                <option value="Iluminacao" <?php if(($perfil['categoria']??'')=='Iluminacao') echo 'selected'; ?>>Iluminação</option>
            </select>
        </div>
        <div class="form-group">
            <label>Localização</label>
            <input type="text" name="localizacao" value="<?php echo htmlspecialchars($perfil['localizacao']??''); ?>" required>
        </div>
        <div class="form-group">
            <label for="faixa_preco">Faixa de Preço Base (R$)</label>
            <select name="faixa_preco" id="faixa_preco" required style="width: 100%; padding: 8px;">
                <option value="">Selecione a sua faixa de preço</option>
                <option value="100" <?php if(($perfil['faixa_preco']??'')=='100') echo 'selected'; ?>>R$ 100 a R$ 599</option>
                <option value="600" <?php if(($perfil['faixa_preco']??'')=='600') echo 'selected'; ?>>R$ 600 a R$ 1099</option>
                <option value="1100" <?php if(($perfil['faixa_preco']??'')=='1100') echo 'selected'; ?>>R$ 1100 a R$ 1599</option>
                <option value="1600" <?php if(($perfil['faixa_preco']??'')=='1600') echo 'selected'; ?>>R$ 1600 a R$ 2099</option>
                <option value="2100" <?php if(($perfil['faixa_preco']??'')=='2100') echo 'selected'; ?>>R$ 2100 a R$ 2599</option>
                <option value="2600" <?php if(($perfil['faixa_preco']??'')=='2600') echo 'selected'; ?>>R$ 2600 a R$ 3099</option>
                <option value="3100" <?php if(($perfil['faixa_preco']??'')=='3100') echo 'selected'; ?>>R$ 3100 a R$ 3599</option>
                <option value="3600" <?php if(($perfil['faixa_preco']??'')=='3600') echo 'selected'; ?>>R$ 3600 a R$ 4099</option>
                <option value="4100" <?php if(($perfil['faixa_preco']??'')=='4100') echo 'selected'; ?>>R$ 4100 a R$ 4599</option>
                <option value="4600" <?php if(($perfil['faixa_preco']??'')=='4600') echo 'selected'; ?>>R$ 4600 a R$ 5099</option>
                <option value="5100" <?php if(($perfil['faixa_preco']??'')=='5100') echo 'selected'; ?>>R$ 5100 a R$ 5599</option>
                <option value="5600" <?php if(($perfil['faixa_preco']??'')=='5600') echo 'selected'; ?>>R$ 5600 a R$ 6099</option>
                <option value="6100" <?php if(($perfil['faixa_preco']??'')=='6100') echo 'selected'; ?>>R$ 6100 a R$ 6599</option>
                <option value="6600" <?php if(($perfil['faixa_preco']??'')=='6600') echo 'selected'; ?>>R$ 6600 a R$ 7099</option>
                <option value="7100" <?php if(($perfil['faixa_preco']??'')=='7100') echo 'selected'; ?>>R$ 7100 a R$ 7599</option>
                <option value="8000" <?php if(($perfil['faixa_preco']??'')=='8000') echo 'selected'; ?>>R$ 7100 a R$ 8099</option>
                <option value="8500" <?php if(($perfil['faixa_preco']??'')=='8500') echo 'selected'; ?>>R$ 7100 a R$ 8599</option>
                <option value="9000" <?php if(($perfil['faixa_preco']??'')=='9000') echo 'selected'; ?>>R$ 7100 a R$ 9099</option>
                <option value="9500" <?php if(($perfil['faixa_preco']??'')=='9500') echo 'selected'; ?>>R$ 7100 a R$ 9599</option>
                <option value="10000" <?php if(($perfil['faixa_preco']??'')=='10000') echo 'selected'; ?>>R$ 7100 a R$ 10099</option>
            </select>
        </div>
        <div class="form-group">
            <label>WhatsApp (Somente números com DDD)</label>
            <input type="text" name="contato_whatsapp" value="<?php echo htmlspecialchars($perfil['contato_whatsapp']??''); ?>">
        </div>
        <div class="form-group">
            <label>Descrição do Serviço</label>
            <textarea name="descricao" rows="3"><?php echo htmlspecialchars($perfil['descricao']??''); ?></textarea>
        </div>
        <button type="submit" class="btn">Salvar Perfil</button>
    </form>
</div>

<!-- Gestão da Galeria de Fotos -->
<div class="form-box" style="margin: 20px 0; max-width: 100%;">
    <h3>Minha Galeria de Trabalhos</h3>
    
    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
        <input type="hidden" name="upload_foto" value="1">
        <div class="form-group">
            <label>Título / Legenda Padrão (Opcional)</label>
            <input type="text" name="titulo" placeholder="Ex: Trabalhos Recentes">
        </div>
        <div class="form-group">
            <label>Selecione uma ou mais imagens (Segure Ctrl / Shift para selecionar várias)</label>
            <input type="file" name="fotos[]" accept="image/*" multiple required>
        </div>
        <button type="submit" class="btn">Publicar Fotos</button>
    </form>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-top: 15px;">
        <?php if (count($galeria) > 0): ?>
            <?php foreach ($galeria as $item): ?>
                <div style="border: 1px solid #ddd; padding: 8px; border-radius: 6px; text-align: center; background: #fff;">
                    <img src="<?php echo htmlspecialchars($item['caminho_imagem']); ?>" style="width: 100%; height: 130px; object-fit: cover; border-radius: 4px;">
                    <p style="font-size: 0.85rem; margin: 5px 0; font-weight: bold;"><?php echo htmlspecialchars($item['titulo']); ?></p>
                    <a href="painel-profissional.php?deletar_foto=<?php echo $item['id']; ?>" onclick="return confirm('Deseja excluir esta foto?');" style="color: red; font-size: 0.8rem; text-decoration: none;">Excluir Foto</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666; font-size: 0.9rem;">Nenhuma foto publicada até o momento.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Gestão de Orçamentos Recebidos -->
<h3>Solicitações de Orçamento Recebidas</h3>

<?php if (count($orcamencos) > 0): ?>
    <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 15px;">
        <?php foreach ($orcamencos as $orc): ?>
            <div style="background: #fff; border: 1px solid #dcdfe6; padding: 20px; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4>Cliente: <?php echo htmlspecialchars($orc['cliente_nome']); ?> (<?php echo htmlspecialchars($orc['cliente_email']); ?>)</h4>
                    <span style="padding: 4px 10px; border-radius: 4px; font-weight: bold; background: <?php echo $orc['status'] === 'aceito' ? '#d4edda' : ($orc['status'] === 'recusado' ? '#f8d7da' : '#fff3cd'); ?>;">
                        Status: <?php echo ucfirst($orc['status']); ?>
                    </span>
                </div>

                <p><strong>Data do Evento:</strong> <?php echo date('d/m/Y', strtotime($orc['data_evento'])); ?> | <strong>Tipo:</strong> <?php echo htmlspecialchars($orc['tipo_evento']); ?></p>
                <p><strong>Mensagem do Cliente:</strong> <?php echo nl2br(htmlspecialchars($orc['mensagem'])); ?></p>
                
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">

                <!-- Formulário para Responder Orçamento -->
                <form method="POST" style="background: #f9f9f9; padding: 15px; border-radius: 6px;">
                    <input type="hidden" name="responder_orcamento" value="1">
                    <input type="hidden" name="orcamento_id" value="<?php echo $orc['id']; ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                        <div>
                            <label><strong>Status da Resposta:</strong></label>
                            <select name="status" style="width: 100%; padding: 8px; margin-top: 5px;">
                                <option value="pendente" <?php if($orc['status']=='pendente') echo 'selected'; ?>>Pendente</option>
                                <option value="aceito" <?php if($orc['status']=='aceito') echo 'selected'; ?>>Aceito / Aprovado</option>
                                <option value="recusado" <?php if($orc['status']=='recusado') echo 'selected'; ?>>Recusado / Indisponível</option>
                            </select>
                        </div>
                        <div>
                            <label><strong>Valor Proposto (R$):</strong></label>
                            <input type="number" step="100" name="valor_proposto" value="<?php echo htmlspecialchars($orc['valor_proposto']??''); ?>" placeholder="Ex: 1200.00" style="width: 100%; padding: 8px; margin-top: 5px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 10px;">
                        <label><strong>Mensagem de Resposta ao Cliente:</strong></label>
                        <textarea name="resposta_profissional" rows="2" placeholder="Escreva aqui detalhes do orçamento, condições de pagamento ou motivo de recusa..." style="width: 100%; padding: 8px; margin-top: 5px;"><?php echo htmlspecialchars($orc['resposta_profissional']??''); ?></textarea>
                    </div>

                    <button type="submit" class="btn" style="background: #27ae60;">Enviar Resposta</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p style="margin-top: 10px;">Você ainda não recebeu nenhuma solicitação de orçamento.</p>
<?php endif; ?>

</div>
</body>
</html>