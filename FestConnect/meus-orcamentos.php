<?php
// RF10, RF12 - Painel onde o Cliente visualiza os orçamentos e as respostas enviadas pelos profissionais
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Verificar se o usuário está logado e se é um cliente
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

$cliente_id = $_SESSION['usuario_id'];

// Buscar todas as solicitações de orçamento do cliente com a resposta do profissional
$stmt = $pdo->prepare("
    SELECT o.*, 
           p.id AS perfil_id, 
           p.categoria, 
           u.nome AS profissional_nome, 
           p.contato_whatsapp
    FROM orcamencos o
    JOIN perfis_profissionais p ON o.profissional_id = p.id
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE o.cliente_id = :cid
    ORDER BY o.id DESC
");
$stmt->execute([':cid' => $cliente_id]);
$solicitacoes = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<h2>Meus Pedidos de Orçamento</h2>

<?php if (count($solicitacoes) > 0): ?>
    <?php foreach ($solicitacoes as $sol): ?>
        <div class="card" style="margin-bottom: 20px; padding: 20px; border-left: 5px solid <?php 
            echo $sol['status'] === 'aceito' ? '#27ae60' : ($sol['status'] === 'recusado' ? '#e74c3c' : '#f39c12'); 
        ?>;">
            <h3>Profissional: <?php echo htmlspecialchars($sol['profissional_nome']); ?> (<?php echo htmlspecialchars($sol['categoria']); ?>)</h3>
            <p><strong>Data do Evento:</strong> <?php echo date('d/m/Y', strtotime($sol['data_evento'])); ?></p>
            <p><strong>Tipo de Evento:</strong> <?php echo htmlspecialchars($sol['tipo_evento']); ?></p>
            <p><strong>Sua Mensagem:</strong> <?php echo nl2br(htmlspecialchars($sol['mensagem'])); ?></p>
            
            <p style="margin-top: 10px;">
                <strong>Status do Pedido:</strong> 
                <span style="font-weight:bold; color: <?php echo $sol['status'] === 'aceito' ? 'green' : ($sol['status'] === 'recusado' ? 'red' : 'orange'); ?>;">
                    <?php 
                        if ($sol['status'] === 'aceito') echo "Orçamento Respondido / Aceito";
                        elseif ($sol['status'] === 'recusado') echo "Recusado / Data Indisponível";
                        else echo "Aguardando Resposta do Profissional";
                    ?>
                </span>
            </p>

            <!-- RESPOSTA DO PROFISSIONAL -->
            <?php if (!empty($sol['valor_resposta']) || !empty($sol['observacao_resposta'])): ?>
                <div style="margin-top: 15px; background: #eafaf1; border: 1px solid #27ae60; padding: 15px; border-radius: 6px;">
                    <h4 style="color: #27ae60; margin-bottom: 5px;">Resposta do Profissional:</h4>
                    
                    <?php if (!empty($sol['valor_resposta'])): ?>
                        <p><strong>Faixa de Valor / Orçamento Estimado:</strong> <span style="font-size: 1.1rem; font-weight: bold; color: #2c3e50;"><?php echo htmlspecialchars($sol['valor_resposta']); ?></span></p>
                    <?php endif; ?>

                    <?php if (!empty($sol['observacao_resposta'])): ?>
                        <p style="margin-top: 5px;"><strong>Detalhes / O que está incluso:</strong> <?php echo nl2br(htmlspecialchars($sol['observacao_resposta'])); ?></p>
                    <?php endif; ?>

                    <!-- RF13 - Contato direto pós-resposta -->
                    <?php if (!empty($sol['contato_whatsapp']) && $sol['status'] === 'aceito'): ?>
                        <p style="margin-top: 12px;">
                            <a href="https://wa.me/<?php echo $sol['contato_whatsapp']; ?>?text=Olá!%20Vi%20sua%20resposta%20ao%20meu%20orçamento%20no%20FestConnect." target="_blank" class="btn" style="background:#25D366;">
                                Falar com o Profissional no WhatsApp
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Você ainda não realizou nenhuma solicitação de orçamento.</p>
<?php endif; ?>

</div>
</body>
</html>