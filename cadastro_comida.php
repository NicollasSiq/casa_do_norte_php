<?php
session_start();
require_once 'autenticacao.php';
verifica_login();
require_once 'db.php';

// Habilita a exibição de erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pdo = conectar();

$msg = '';
$erro = '';
$comida_edicao = null;
$em_edicao = false;

// Processamento de exclusão
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'excluir') {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM movimentacao WHERE id_comida = ?');
        $stmt->execute([$id]);
        $total_movimentacoes = $stmt->fetchColumn();

        if ($total_movimentacoes > 0) {
            $erro = "Não é possível excluir a comida porque existem movimentações registradas.";
        } else {
            $stmt = $pdo->prepare('DELETE FROM comida WHERE id_comida = ?');
            $stmt->execute([$id]);
            $msg = "Comida excluída com sucesso.";
        }
    } catch (PDOException $e) {
        $erro = "Erro ao excluir comida: " . $e->getMessage();
    }
}

// Carrega dados da comida para edição
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'editar') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM comida WHERE id_comida = ?');
    $stmt->execute([$id]);
    $comida_edicao = $stmt->fetch();
    if ($comida_edicao) {
        $em_edicao = true;
    } else {
        $erro = "Comida não encontrada para edição.";
    }
}

// Processa o formulário de cadastro ou atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $origem = trim($_POST['origem'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $porcao = trim($_POST['porcao'] ?? '');
    $calorias = (int)($_POST['calorias'] ?? 0);
    $estoqueminimo = (int)($_POST['estoqueminimo'] ?? 0);
    $quantidadeestoque = (int)($_POST['quantidadeestoque'] ?? 0);

    if ($nome === '' || $estoqueminimo < 0) {
        $erro = 'Por favor, preencha os campos obrigatórios (*) corretamente.';
    } else {
        try {
            if ($id) {
                // ATUALIZAÇÃO
                $stmt = $pdo->prepare("
                    UPDATE comida SET 
                        nome = ?, descricao = ?, categoria = ?, origem = ?, 
                        ingredientes = ?, porcao = ?, calorias = ?, estoque_minimo = ?
                    WHERE id_comida = ?");
                $stmt->execute([
                    $nome, $descricao, $categoria, $origem, $ingredientes,
                    $porcao, $calorias, $estoqueminimo, $id
                ]);
                $msg = "Comida atualizada com sucesso.";
            } else {
                // CADASTRO
                if ($quantidadeestoque < 0) {
                    $erro = 'A quantidade em estoque inicial não pode ser negativa.';
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO comida (nome, descricao, categoria, origem, ingredientes, porcao, calorias, estoque_minimo, quantidade_estoque)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $nome, $descricao, $categoria, $origem, $ingredientes,
                        $porcao, $calorias, $estoqueminimo, $quantidadeestoque
                    ]);
                    $msg = "Comida cadastrada com sucesso.";
                }
            }
        } catch (PDOException $e) {
            $erro = "Erro ao salvar comida: " . $e->getMessage();
        }
    }
}

// Filtro de busca
$termo_busca = trim($_GET['busca'] ?? '');
if (isset($_GET['limpar'])) {
    header('Location: cadastro_comida.php');
    exit;
}

// Busca a lista de comidas para exibição
$params = [];
$sql = "SELECT * FROM comida";
if ($termo_busca) {
    $sql .= " WHERE nome LIKE ? OR categoria LIKE ?";
    $params[] = "%$termo_busca%";
    $params[] = "%$termo_busca%";
}
$sql .= " ORDER BY nome";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$comidas = $stmt->fetchAll();

// Calcula o estoque ATUAL de cada comida
$estoque_atual_comida = [];
foreach ($comidas as $c) {
    $stmtEstoque = $pdo->prepare("
        SELECT quantidade_estoque + IFNULL((
            SELECT SUM(CASE WHEN tipo_movimentacao='entrada' THEN quantidade ELSE -quantidade END)
            FROM movimentacao WHERE id_comida = ?
        ), 0) AS estoque_atual
        FROM comida WHERE id_comida = ?
    ");
    $stmtEstoque->execute([$c['id_comida'], $c['id_comida']]);
    $estoque_atual_comida[$c['id_comida']] = (int)$stmtEstoque->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Cadastro de Comida</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'header.php'; ?>

<h2>Cadastro de Comida</h2>

<?php if ($msg): ?>
    <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($erro): ?>
    <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
<?php endif; ?>

<form method="post" action="cadastro_comida.php<?php if ($em_edicao) echo '?acao=editar&id=' . $comida_edicao['id_comida']; ?>">
    <input type="hidden" name="id" value="<?php echo $comida_edicao['id_comida'] ?? ''; ?>" />
    
    <label for="nome">Nome*:</label>
    <input type="text" id="nome" name="nome" maxlength="100" required value="<?php echo htmlspecialchars($comida_edicao['nome'] ?? ''); ?>" />
    
    <label for="descricao">Descrição:</label>
    <textarea id="descricao" name="descricao"><?php echo htmlspecialchars($comida_edicao['descricao'] ?? ''); ?></textarea>
    
    <label for="categoria">Categoria:</label>
    <input type="text" id="categoria" name="categoria" maxlength="50" value="<?php echo htmlspecialchars($comida_edicao['categoria'] ?? ''); ?>" />
    
    <label for="origem">Origem (Região/Estado):</label>
    <input type="text" id="origem" name="origem" maxlength="50" value="<?php echo htmlspecialchars($comida_edicao['origem'] ?? ''); ?>" />
    
    <label for="ingredientes">Ingredientes Principais:</label>
    <textarea id="ingredientes" name="ingredientes"><?php echo htmlspecialchars($comida_edicao['ingredientes'] ?? ''); ?></textarea>
    
    <label for="porcao">Porção:</label>
    <input type="text" id="porcao" name="porcao" maxlength="50" value="<?php echo htmlspecialchars($comida_edicao['porcao'] ?? ''); ?>" />
    
    <label for="calorias">Calorias (kcal):</label>
    <input type="number" id="calorias" min="0" name="calorias" value="<?php echo htmlspecialchars($comida_edicao['calorias'] ?? ''); ?>" />
    
    <label for="estoqueminimo">Estoque Mínimo*:</label>
    <input type="number" id="estoqueminimo" min="0" name="estoqueminimo" required value="<?php echo htmlspecialchars($comida_edicao['estoque_minimo'] ?? ''); ?>" />
    
    <label for="quantidadeestoque">Quantidade em Estoque (Inicial)*:</label>
    <input type="number" id="quantidadeestoque" min="0" name="quantidadeestoque" required value="<?php echo htmlspecialchars($comida_edicao['quantidade_estoque'] ?? '0'); ?>"
    <?php echo ($em_edicao ? 'readonly' : ''); ?> />
    <?php if ($em_edicao): ?>
        <div class="info-msg">A quantidade só pode ser alterada via Gestão de Estoque.</div>
    <?php endif; ?>
    <br> <br> <br>
    <button type="submit"><?php echo $em_edicao ? 'Atualizar Comida' : 'Cadastrar Comida'; ?></button> 
    <?php if ($em_edicao): ?>
        <a href="cadastro_comida.php" style="margin-left: 10px;">Cancelar Edição</a>
    <?php endif; ?>
</form>
<br>
<hr />
<form method="get" action="cadastro_comida.php">
    <label for="busca">Buscar por nome ou categoria:</label>
    <input type="text" id="busca" name="busca" value="<?php echo htmlspecialchars($termo_busca); ?>" />
    <button type="submit">Buscar</button>
    <button type="submit" name="limpar" value="1">Limpar</button>
</form>
<br> <br>

<table border="1">
    <thead>
        <tr>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Estoque Mínimo</th>
            <th>Estoque Atual</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($comidas): ?>
            <?php foreach ($comidas as $c):
                $estoque_atual = $estoque_atual_comida[$c['id_comida']];
                $classe = ($estoque_atual <= $c['estoque_minimo']) ? 'estoque-baixo' : '';
            ?>
                <tr class="<?php echo $classe; ?>">
                    <td><?php echo htmlspecialchars($c['nome']); ?></td>
                    <td><?php echo htmlspecialchars($c['categoria']); ?></td>
                    <td><?php echo (int)$c['estoque_minimo']; ?></td>
                    <td><?php echo $estoque_atual; ?></td>
                    <td>
                        <a href="cadastro_comida.php?acao=editar&id=<?php echo (int)$c['id_comida']; ?>">Editar</a> |
                        <a href="cadastro_comida.php?acao=excluir&id=<?php echo (int)$c['id_comida']; ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">Nenhuma comida encontrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>
</body>
</html>