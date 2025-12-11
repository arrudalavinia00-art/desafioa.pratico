<?php
// principal.php

session_start();

// Verifica se o usuário está logado (Controle de Acesso)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php"); // Redireciona para o login (Entrega 4)
    exit();
}

// O nome do usuário é recuperado da sessão (RF02)
$nome_usuario = $_SESSION['nome_usuario']; 

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Estoque - Principal</title>
</head>
<body>
    <h1>Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h1>

    <nav>
        <ul>
            <li><a href="cadastro_produto.php">Cadastro de Produto</a></li>
            <li><a href="gestao_estoque.php">Gestão de Estoque</a></li>
            <li><a href="logout.php" style="color: red;">Sair (Logout)</a></li>
        </ul>
    </nav>
</body>
</html>