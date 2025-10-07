<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Casa do Norte - Controle de Estoque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Controle de Estoque - Casa do Norte</h1>
    <nav>
        <a href="principal.php">Página Principal</a>
        <a href="cadastro_comida.php">Cadastro de Comida</a>
        <a href="gestao_estoque.php">Gestão de Estoque</a>
        <a href="logout.php">Sair</a>
    </nav>
    <p style="padding: 5px;">Bem-vindo(a), <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong></p>
</header>

<hr>


