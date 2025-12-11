<?php
// conexao.php

// ADAPTE ESTAS VARIÁVEIS COM SUAS CREDENCIAIS!
$servidor = "localhost";
$usuario = "seu_usuario_do_mysql"; // Ex: root
$senha = "sua_senha_do_mysql";    // Ex: "" (vazio, se não houver senha)
$banco = "saep_db";

// Tenta conectar ao MySQL
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o Banco de Dados: " . $conn->connect_error);
}

// Opcional: Define o charset para garantir acentuação correta
$conn->set_charset("utf8");

// A partir daqui, você pode usar a variável $conn para fazer consultas SQL.
?>