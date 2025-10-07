<?php
session_start();
require_once 'autenticacao.php';
verifica_login(); // Garante que só usuários logados acessem
require_once 'header.php';
?>
<p>Utilize o menu de navegação acima para cadastrar comidas ou gerenciar o estoque.</p>