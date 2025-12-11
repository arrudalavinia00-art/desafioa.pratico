<?php
// logout.php
session_start();
session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Destrói a sessão
header("Location: index.php"); // Redireciona para o Login (Entrega 4)
exit();
?>