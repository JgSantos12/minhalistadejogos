CREATE DATABASE IF NOT EXISTS jogos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jogos;

CREATE TABLE IF NOT EXISTS jogos (
    id_jogo INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL,
    descricao TEXT NULL,
    genero VARCHAR(50) NOT NULL,
    status VARCHAR(13) NOT NULL DEFAULT 'Planejo Jogar',
    nota TINYINT UNSIGNED NULL,
    horas_jogadas INT UNSIGNED NOT NULL DEFAULT 0,
    data_inicio DATE NOT NULL DEFAULT (CURRENT_DATE),
    data_fim DATE NULL,
    favorito TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS plataformas (
    id_plataforma INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS jogos_plataformas (
    id_jogo INT UNSIGNED NOT NULL,
    id_plataforma INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_jogo, id_plataforma),
    FOREIGN KEY (id_jogo) REFERENCES jogos(id_jogo) ON DELETE CASCADE,
    FOREIGN KEY (id_plataforma) REFERENCES plataformas(id_plataforma) ON DELETE CASCADE
);

INSERT INTO plataformas (nome) VALUES 
    ('PC'),
    ('PlayStation 4'),
    ('PlayStation 5'),
    ('Xbox One'),
    ('Xbox Series X/S'),
    ('Nintendo Switch'),
    ('Mobile');

INSERT INTO jogos (nome, descricao, genero, status, nota, horas_jogadas, data_inicio, data_fim, favorito) VALUES
    ('The Legend of Zelda: Breath of the Wild', 'Ação e aventura em mundo aberto', 'Aventura', 'Jogando', 10, 120, '2023-01-15', NULL, 1),
    ('God of War', 'Aventura épica com Kratos e Atreus', 'Ação', 'Zerado', 9, 50, '2022-11-01', '2022-12-15', 0),
    ('Hollow Knight', 'Metroidvania desafiador com belos gráficos', 'Puzzle e Plataforma', 'Planejo Jogar', NULL, 0, '2023-02-10', NULL, 0);

INSERT INTO jogos_plataformas (id_jogo, id_plataforma) VALUES
    (1, 6), -- The Legend of Zelda: Breath of the Wild - Nintendo Switch
    (2, 2), -- God of War - PlayStation 4
    (3, 1), -- Hollow Knight - PC
    (3, 6); -- Hollow Knight - Nintendo Switch