<?php
session_start();
require_once 'autenticacao.php';
verifica_login();
require_once 'db.php';
$pdo = conectar();

// Habilita exibição de erros
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$msg = '';
$erro = '';
$alerta_estoque_baixo = '';

$usuario_id = $_SESSION['usuario_id'] ?? null;

// Processa o formulário de movimentação de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comida = (int)($_POST['idcomida'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $quantidade = (int)($_POST['quantidade'] ?? 0);
    $data_movimentacao = $_POST['datamovimentacao'] ?? '';
    $observacao = trim($_POST['observacao'] ?? '');

    // Validações básicas
    if (!$id_comida || !$tipo || $quantidade <= 0 || !$data_movimentacao) {
        $erro = 'Preencha todos os campos obrigatórios (*) corretamente.';
    } else {
        // Se for uma SAÍDA, verifica se há estoque suficiente
        if ($tipo === 'saida') {
            $stmt_estoque = $pdo->prepare("
                SELECT (c.quantidade_estoque + IFNULL(SUM(CASE WHEN m.tipo_movimentacao = 'entrada' THEN m.quantidade ELSE -m.quantidade END), 0)) AS estoque_atual
                FROM comida c
                LEFT JOIN movimentacao m ON c.id_comida = m.id_comida
                WHERE c.id_comida = ?
                GROUP BY c.id_comida
            ");
            $stmt_estoque->execute([$id_comida]);
            $estoque_atual = (int)$stmt_estoque->fetchColumn();

            if ($quantidade > $estoque_atual) {
                $erro = 'Estoque insuficiente para esta saída. Estoque atual: ' . $estoque_atual;
            }
        }
        
        // Se não houver erros, insere a movimentação
        if (!$erro) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO movimentacao (id_comida, id_usuario, data_movimentacao, tipo_movimentacao, quantidade, observacao)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id_comida, $usuario_id, $data_movimentacao, $tipo, $quantidade, $observacao]);
                $msg = 'Movimentação registrada com sucesso.';

                // Após uma saída, verifica se o estoque ficou abaixo do mínimo para gerar alerta
                if ($tipo === 'saida') {
                    $stmt_check = $pdo->prepare("
                        SELECT 
                            (c.quantidade_estoque + IFNULL((SELECT SUM(CASE WHEN m.tipo_movimentacao = 'entrada' THEN m.quantidade ELSE -m.quantidade END) FROM movimentacao m WHERE m.id_comida = c.id_comida), 0)) as estoque_final,
                            c.estoque_minimo,
                            c.nome
                        FROM comida c
                        WHERE c.id_comida = ?
                    ");
                    $stmt_check->execute([$id_comida]);
                    $resultado = $stmt_check->fetch();
                    if ($resultado && $resultado['estoque_final'] <= $resultado['estoque_minimo']) {
                        $alerta_estoque_baixo = "Atenção: O estoque de '" . htmlspecialchars($resultado['nome']) . "' está baixo (" . $resultado['estoque_final'] . " unidades).";
                    }
                }

            } catch (PDOException $e) {
                $erro = "Erro ao registrar movimentação: " . $e->getMessage();
            }
        }
    }
}

// Lista as comidas em ordem alfabética para o formulário
$stmt_comidas = $pdo->query("SELECT id_comida, nome FROM comida ORDER BY nome ASC");
$comidas = $stmt_comidas->fetchAll();

// Calcula o estoque atual de todas as comidas para a tabela de resumo
$stmt_estoques = $pdo->query("
    SELECT 
        c.id_comida,
        c.nome,
        c.categoria,
        c.estoque_minimo,
        (c.quantidade_estoque + IFNULL((SELECT SUM(CASE WHEN m.tipo_movimentacao = 'entrada' THEN m.quantidade ELSE -m.quantidade END) FROM movimentacao m WHERE m.id_comida = c.id_comida), 0)) as estoque_atual
    FROM comida c
    ORDER BY c.nome
");
$estoques = $stmt_estoques->fetchAll();


// Busca o histórico das últimas 50 movimentações
$stmt_historico = $pdo->query("
    SELECT m.data_movimentacao, c.nome AS nome_comida, m.tipo_movimentacao, m.quantidade, m.observacao, u.nome AS nome_usuario
    FROM movimentacao m
    JOIN comida c ON m.id_comida = c.id_comida
    JOIN usuario u ON m.id_usuario = u.id_usuario
    ORDER BY m.id_movimentacao DESC
    LIMIT 50
");
$historico = $stmt_historico->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Gestão de Estoque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php require_once 'header.php'; ?>

<h2>Movimentação de Estoque</h2>

<?php if ($msg): ?>
    <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($erro): ?>
    <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
<?php endif; ?>
<?php if ($alerta_estoque_baixo): ?>
    <div class="alerta-estoque"><?php echo htmlspecialchars($alerta_estoque_baixo); ?></div>
<?php endif; ?>

<form method="post" action="gestao_estoque.php">
    <label for="idcomida">Comida*:</label>
    <select id="idcomida" name="idcomida" required>
        <option value="">Selecione...</option>
        <?php foreach ($comidas as $c): ?>
            <option value="<?php echo (int)$c['id_comida']; ?>">
                <?php echo htmlspecialchars($c['nome']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <label for="tipo">Tipo de Movimentação*:</label>
    <select id="tipo" name="tipo" required>
        <option value="">Selecione...</option>
        <option value="entrada">Entrada</option>
        <option value="saida">Saída</option>
    </select>
    
    <label for="quantidade">Quantidade*:</label>
    <input type="number" id="quantidade" name="quantidade" required min="1" />
    
    <label for="datamovimentacao">Data*:</label>
    <input type="date" id="datamovimentacao" name="datamovimentacao" required value="<?php echo date('Y-m-d'); ?>" />
    
    <label for="observacao">Observação:</label>
    <textarea id="observacao" name="observacao" maxlength="200"></textarea>
    <br> <br> <br>
    <button type="submit">Registrar Movimentação</button>
</form>
<br>
<hr/>

<h3>Estoque Atual</h3>
<table border="1">
    <thead>
        <tr>
            <th>Comida</th>
            <th>Categoria</th>
            <th>Estoque Atual</th>
            <th>Estoque Mínimo</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($estoques): ?>
            <?php foreach ($estoques as $e): ?>
                <tr class="<?php echo ((int)$e['estoque_atual'] <= (int)$e['estoque_minimo']) ? 'estoque-baixo' : ''; ?>">
                    <td><?php echo htmlspecialchars($e['nome']); ?></td>
                    <td><?php echo htmlspecialchars($e['categoria']); ?></td>
                    <td><?php echo (int)$e['estoque_atual']; ?></td>
                    <td><?php echo (int)$e['estoque_minimo']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhuma comida cadastrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<hr/>

<h3>Histórico das Últimas 50 Movimentações</h3>
<table border="1">
    <thead>
        <tr>
            <th>Data</th>
            <th>Comida</th>
            <th>Tipo</th>
            <th>Quantidade</th>
            <th>Observação</th>
            <th>Usuário Responsável</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($historico): ?>
            <?php foreach ($historico as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($h['data_movimentacao']))); ?></td>
                    <td><?php echo htmlspecialchars($h['nome_comida']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($h['tipo_movimentacao'])); ?></td>
                    <td><?php echo (int)$h['quantidade']; ?></td>
                    <td><?php echo htmlspecialchars($h['observacao']); ?></td>
                    <td><?php echo htmlspecialchars($h['nome_usuario']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6">Nenhuma movimentação registrada.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>
</body>
</html>