<?php
// gestao_estoque.php

session_start();
include 'conexao.php'; // Garante a conexão $conn

// Verifica se o usuário está logado (Controle de Acesso)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Variável para armazenar mensagens de alerta (seja de erro de BD ou Estoque Mínimo)
$mensagem = "";

// ----------------------------------------------------------------------
// 1. LÓGICA DE PROCESSAMENTO DE MOVIMENTAÇÃO (POST)
// ----------------------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Coleta de dados
    $produto_id = $_POST['produto_id'];
    $tipo = $_POST['tipo_movimentacao']; // 'E' ou 'S'
    $quantidade = floatval($_POST['quantidade']);
    $data_movimentacao = $_POST['data_movimentacao'];
    $data_validade = $_POST['data_validade'] ?? NULL; // RF07: Data de validade opcional
    $id_usuario = $_SESSION['id_usuario']; // RF09: Responsável
    
    // Validação básica
    if ($quantidade <= 0) {
        $mensagem = "A quantidade deve ser maior que zero.";
    } else {
        // 2. Transação (garante que ambas as operações sejam feitas ou nenhuma)
        $conn->begin_transaction();
        try {
            // A. Inserir na MOVIMENTACAO (RF06/RF09)
            $sql_mov = "INSERT INTO MOVIMENTACAO (id_produto, id_usuario, tipo, quantidade, data_movimentacao, data_validade) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_mov = $conn->prepare($sql_mov);
            $stmt_mov->bind_param("iisdss", $produto_id, $id_usuario, $tipo, $quantidade, $data_movimentacao, $data_validade);
            $stmt_mov->execute();
            $stmt_mov->close();

            // B. Atualizar Estoque em PRODUTO
            $operador = ($tipo == 'E') ? '+' : '-';
            $sql_update = "UPDATE PRODUTO SET estoque_atual = estoque_atual {$operador} ? WHERE id_produto = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("di", $quantidade, $produto_id);
            $stmt_update->execute();
            $stmt_update->close();

            $alerta = "";
            // C. VERIFICAÇÃO DE ESTOQUE MÍNIMO (RF07) - Apenas se for SAÍDA
            if ($tipo == 'S') {
                // Reconsulta o produto para verificar o novo estoque e o estoque mínimo
                $sql_check = "SELECT nome, estoque_atual, estoque_minimo FROM PRODUTO WHERE id_produto = ?";
                $stmt_check = $conn->prepare($sql_check);
                $stmt_check->bind_param("i", $produto_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $produto_info = $result_check->fetch_assoc();
                $stmt_check->close();

                // Checa o estoque ATUALIZADO
                if ($produto_info['estoque_atual'] < $produto_info['estoque_minimo']) {
                    $alerta = "ALERTA! O produto '{$produto_info['nome']}' está abaixo do estoque mínimo ({$produto_info['estoque_minimo']}).";
                }
            }
            
            $conn->commit();
            
            // Redireciona para evitar reenvio do formulário e mostra o alerta
            header("Location: gestao_estoque.php?sucesso=true&alerta=" . urlencode($alerta));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao registrar movimentação: " . $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------------
// 2. LÓGICA DE EXIBIÇÃO (GET)
// ----------------------------------------------------------------------

// Se houver um alerta de sucesso na URL (após o POST)
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'true') {
    $mensagem = "Movimentação registrada com sucesso!";
    if (!empty($_GET['alerta'])) {
        $alerta_estoque_minimo = htmlspecialchars(urldecode($_GET['alerta']));
    }
}

// RF07: Listar produtos em ordem alfabética para o <select>
$sql_produtos = "SELECT id_produto, nome, estoque_atual, estoque_minimo FROM PRODUTO ORDER BY nome ASC"; 
$result_produtos = $conn->query($sql_produtos);
$produtos = $result_produtos->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Estoque</title>
</head>
<body>
    <h2>Gestão de Estoque</h2>
    <p><a href="principal.php">Voltar para o Menu Principal</a></p>

    <?php if (!empty($mensagem)): ?>
        <p style="color: green;"><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <?php if (isset($alerta_estoque_minimo) && !empty($alerta_estoque_minimo)): ?>
        <h3 style="color: red; border: 2px solid red; padding: 10px;">
            <?php echo $alerta_estoque_minimo; ?>
        </h3>
    <?php endif; ?>

    <h3>Registrar Movimentação</h3>
    <form method="POST" action="gestao_estoque.php">
        <div>
            <label for="produto_id">Produto (Ordem Alfabética):</label>
            <select name="produto_id" required>
                <?php foreach ($produtos as $prod): ?>
                <option value="<?php echo $prod['id_produto']; ?>">
                    <?php echo htmlspecialchars($prod['nome']); ?> (Estoque: <?php echo $prod['estoque_atual']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="tipo_movimentacao">Tipo:</label>
            <select name="tipo_movimentacao" required>
                <option value="E">Entrada</option>
                <option value="S">Saída</option>
            </select>
        </div>
        <div>
            <label for="quantidade">Quantidade:</label>
            <input type="number" name="quantidade" step="0.01" required>
        </div>
        <div>
            <label for="data_movimentacao">Data da Movimentação:</label>
            <input type="date" name="data_movimentacao" required>
        </div>
        <div>
            <label for="data_validade">Data de Validade (Opcional):</label>
            <input type="date" name="data_validade">
        </div>
        <button type="submit">Registrar Movimentação</button>
    </form>
</body>
</html>