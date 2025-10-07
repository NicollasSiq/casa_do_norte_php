<?php
require_once 'db.php';

/**
 * Autentica um usuário e armazena seus dados na sessão.
 */
function autenticar($login, $senha) {
    $pdo = conectar();
    $stmt = $pdo->prepare('SELECT id_usuario, nome, senha FROM usuario WHERE login = ?');
    $stmt->execute([$login]);
    $usuario = $stmt->fetch();
    
    // Compara a senha fornecida (em MD5) com a armazenada no banco
    if ($usuario && md5($senha) === $usuario['senha']) {
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        return true;
    }
    return false;
}

/**
 * Verifica se o usuário está logado; caso contrário, redireciona para a tela de login.
 */
function verifica_login() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Realiza o logout do usuário, limpando a sessão.
 */
function logout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}