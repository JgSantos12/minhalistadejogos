<?php
// Conexão com o MariaDB via PDO (padrão do XAMPP: usuário root, sem senha)
function conectar(): PDO
{
    return new PDO(
        'mysql:host=localhost;dbname=jogos;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erros viram exceções
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // linhas como array associativo
            PDO::ATTR_EMULATE_PREPARES   => false,                  // prepared statements reais
        ]
    );
}
