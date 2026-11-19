<?php
// RF01, RNF04, RNF09 - Cadastro com validação e Hash de Senha
require_once 'config/database.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo  = $_POST['tipo'];

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $senhaHash = password_hash($senha, PASSWORD_ARGON2ID); // RNF04
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
            $stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash, ':tipo' => $tipo]);
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $erro = "E-mail já cadastrado ou erro no sistema.";
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
require_once 'includes/header.php';
?>

<div class="form-box">
    <h2>Criar Conta no FestConnect</h2>
    <?php if ($erro): ?><p style="color:red;"><?php echo $erro; ?></p><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" required>
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" required>
        </div>
        <div class="form-group">
            <label>Tipo de Conta</label>
            <select name="tipo">
                <option value="cliente">Cliente (Quero contratar)</option>
                <option value="profissional">Profissional (Quero oferecer serviços)</option>
            </select>
        </div>
        <button type="submit" class="btn">Cadastrar</button>
    </form>
</div>