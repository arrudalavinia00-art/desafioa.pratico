<?php
// cadastro_produto.php

session_start();
include 'conexao.php'; // Inclui a conexão e garante que o BD está disponível

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php"); 
    exit();
}

// --- 1. LÓGICA DE INSERÇÃO / EDIÇÃO / EXCLUSÃO (CRUD) ---
// ... (Você implementará o código PHP aqui para INSERT, UPDATE, DELETE)

// --- 2. LÓGICA DE LISTAGEM E BUSCA (RF04) ---
$produtos = [];
$search_term = '';

// Se houver termo de busca, aplica o filtro (RF04)
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $search_term = '%' . $_GET['busca'] . '%';
    $stmt = $conn->prepare("SELECT * FROM PRODUTO WHERE nome LIKE ?");
    $stmt->bind_param("s", $search_term);
} else {
    // Listagem automática (RF04)
    $stmt = $conn->prepare("SELECT * FROM PRODUTO");
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto</title>
</head>
<body>
    <h2>Cadastro de Produto (CRUD)</h2>
    <p><a href="principal.php">Voltar para o Menu Principal</a></p>

    <h3>Inserir/Editar Produto</h3>
    <form method="POST" action="cadastro_produto.php">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" required>
        <button type="submit">Salvar Produto</button>
    </form>

    <hr>

    <h3>Listagem de Produtos</h3>
    <form method="GET" action="cadastro_produto.php">
        <input type="text" name="busca" placeholder="Buscar por nome..." value="<?php echo htmlspecialchars($_GET['busca'] ?? ''); ?>">
        <button type="submit">Buscar</button>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Estoque Mínimo</th>
                <th>Unidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $produto): ?>
            <tr>
                <td><?php echo $produto['id_produto']; ?></td>
                <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                <td><?php echo $produto['estoque_minimo']; ?></td>
                <td><?php echo $produto['unidade_medida']; ?></td>
                <td>
                    <a href="editar_produto.php?id=<?php echo $produto['id_produto']; ?>">Editar</a> | 
                    <a href="excluir_produto.php?id=<?php echo $produto['id_produto']; ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>