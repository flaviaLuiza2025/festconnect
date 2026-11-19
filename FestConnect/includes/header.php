<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FestConnect - Plataforma de Eventos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header>
    <a href="index.php" class="logo">FestConnect</a>
    <nav>
        <a href="index.php">Buscar Profissionais</a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php if ($_SESSION['usuario_tipo'] === 'profissional'): ?>
                <a href="painel-profissional.php">Meu Painel PRO</a>
            <?php else: ?>
                <a href="meus-orcamentos.php">Meus Orçamentos</a>
            <?php endif; ?>
            <a href="logout.php">Sair (<?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="cadastro.php" class="btn">Cadastrar</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container">