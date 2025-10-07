<?php
// Inicia sessão e importa lógica de autenticação
session_start();
require_once 'autenticacao.php';

// Verifica se o usuário já está logado e redireciona se estiver
if (isset($_SESSION['usuario_id'])) {
    header('Location: principal.php');
    exit;
}

$erro = '';

// Processamento do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $senha = $_POST['senha'] ?? '';
    if ($login && $senha) {
        if (autenticar($login, $senha)) {
            // Se autenticado, redireciona para a página principal
            header('Location: principal.php');
            exit;
        } else {
            // Informa ao usuário sobre a falha
            $erro = 'Login ou senha inválidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Casa do Norte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Controle de Estoque - Casa do Norte</h2>
    <p>Acesse sua conta para gerenciar o estoque:</p>
    
    <?php if ($erro) echo "<div class='erro'>".htmlspecialchars($erro)."</div>"; ?>
    
    <form method="post">
        <label for="login">Usuário:</label>
        <input type="text" name="login" id="login" required>
        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required><br> <br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>