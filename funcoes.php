<?php
// Função de sanitização simples para dados do usuário (se necessário)
function sanitizar_input($dado) {
    return htmlspecialchars(strip_tags(trim($dado)));
}