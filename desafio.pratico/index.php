<?php
// index.php (Entrega 4: Login)

// Inicia a sessão para armazenar dados do usuário (RF02)
session_start();

// Inclui o arquivo de conexão
include 'conexao.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_user = $_POST['login'];
    $senha_user = $_POST['senha'];

    // Prepara a consulta para evitar injeção SQL
    $stmt = $conn->prepare("SELECT id_usuario, nome, senha FROM USUARIO WHERE login = ?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // RF01: Validação da senha (Assumindo que está em texto simples)
        if ($user['senha'] === $senha_user) { 
            // Sucesso! Cria a sessão
            $_SESSION['logged_in'] = true;
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nome_usuario'] = $user['nome']; // RF02
            
            // Redireciona para a página principal (Entrega 5)
            header("Location: principal.php");
            exit();
        } else {
            // RF01: Falha na senha
            $erro = "Login ou senha incorretos. Tente novamente."; 
        }
    } else {
        // RF01: Falha no login
        $erro = "Login ou senha incorretos. Tente novamente.";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Estoque - Login</title>
</head>
<body>
    <h2>Autenticação de Usuário</h2>
    
    <?php if (!empty($erro)): ?>
        <p style="color: red;"><?php echo $erro; ?></p> <?php endif; ?>

    <form method="POST" action="index.php">
        <div>
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
        </div>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>