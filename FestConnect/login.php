<?php
// RF02, RNF04, RNF05 - Autenticação
require_once 'config/database.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_start();
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];

        header("Location: " . ($usuario['tipo'] === 'profissional' ? "painel-profissional.php" : "index.php"));
        exit;
    } else {
        $erro = "E-mail ou senha inválidos.";
    }
}
require_once 'includes/header.php';
?>

<div class="form-box">
    <h2>Entrar</h2>
    <?php if ($erro): ?><p style="color:red;"><?php echo $erro; ?></p><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>
        <button type="submit" class="btn">Entrar</button>
    </form>
</div>