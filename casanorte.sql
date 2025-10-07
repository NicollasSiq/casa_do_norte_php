-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS casanorte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE casanorte;

-- Tabela de usuários para controle de acesso
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(32) NOT NULL 
) ENGINE=InnoDB;

-- Tabela de comidas (produtos do restaurante)
CREATE TABLE IF NOT EXISTS comida (
    id_comida INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    categoria VARCHAR(50),
    origem VARCHAR(50),
    ingredientes TEXT,
    porcao VARCHAR(50),
    calorias INT,
    quantidade_estoque INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Tabela de movimentação de estoque
-- Registra todas as entradas e saídas de comidas
CREATE TABLE IF NOT EXISTS movimentacao (
    id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
    id_comida INT NOT NULL,
    id_usuario INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    tipo_movimentacao ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    observacao VARCHAR(200),
    FOREIGN KEY (id_comida) REFERENCES comida(id_comida),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- Inserção de dados de exemplo (mínimo de 3 por tabela) 

-- Usuários (senha para todos é '123' em MD5)
INSERT INTO usuario (nome, login, senha) VALUES
('IgStation', 'ig', '202cb962ac59075b964b07152d234b70'),
('Tiozinho', 'tio', '202cb962ac59075b964b07152d234b70'),
('Long hair', 'hair', '202cb962ac59075b964b07152d234b70');

-- Comidas
INSERT INTO comida (nome, descricao, categoria, origem, ingredientes, porcao, calorias, quantidade_estoque, estoque_minimo) VALUES
('Baião de Dois', 'Prato de arroz com feijão verde, queijo coalho e carne seca desfiada.', 'Prato Principal', 'Ceará', 'Arroz, feijão verde, carne seca, queijo coalho, coentro, cebola.', 'Serve 2 pessoas', 550, 15, 5),
('Acarajé', 'Bolinho de feijão fradinho frito no azeite de dendê, recheado com vatapá e camarão.', 'Salgado', 'Bahia', 'Feijão fradinho, cebola, azeite de dendê, camarão seco.', 'Unidade', 310, 30, 10),
('Moqueca de Peixe', 'Ensopado de peixe com leite de coco, azeite de dendê e pimentões.', 'Prato Principal', 'Bahia', 'Peixe branco, leite de coco, pimentões, tomate, coentro, azeite de dendê.', 'Serve 1 pessoa', 480, 10, 3);

-- Movimentações de exemplo
INSERT INTO movimentacao (id_comida, id_usuario, data_movimentacao, tipo_movimentacao, quantidade, observacao) VALUES
(1, 1, '2025-10-01', 'entrada', 5, 'Compra semanal'),
(2, 2, '2025-10-02', 'saida', 10, 'Venda para evento'),
(3, 1, '2025-10-03', 'entrada', 8, 'Recebimento do fornecedor');