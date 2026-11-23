<?php
require __DIR__ . '/cliente.php';

$erro = null;

$r = chamar_api('GET', null, null, ['estatisticas' => 1]);

if ($r['status'] === 200) {
    $estatisticas = $r['dados'];
} else {
    $erro = $r['dados']['erro'] ?? 'Não foi possível carregar as suas estatísticas';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="adicionar">
    <main class="card__detalhes">
        <div class="banner detalhes__banner">

        </div>
        <div class="detalhes__container">
            <h2 class="cabecalho__titulo">Estatísticas do Jogador</h2>
            <?php if ($erro): ?>
                <div class="detalhes__mensagem-erro">
                    <p class="erro">⚠️
                        <?= h($erro) ?>
                    </p>
                    <a class="jogos__link" href="index.php">Voltar</a>
                </div>
            <?php endif; ?>
            <div class="detalhes__dados">
                <div class="jogo__dados">
                    <img src="assets/iconNome.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Total de Jogos Cadastrados:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php
                            echo number_format($estatisticas['total_jogos'], 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
                <div class="jogo__dados">
                    <img src="assets/iconJogoZerado.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Total de Jogos Zerados:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php
                            echo number_format($estatisticas['jogos_zerados'], 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
                <div class="jogo__dados">
                    <img src="assets/iconHoras.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Total de Horas Jogadas:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php
                            echo number_format($estatisticas['total_horas'], 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
                <div class="jogo__dados">
                    <img src="assets/iconMediaHora.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Horas Médias por Jogo:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php if ($estatisticas['media_horas'] !== null): ?>
                                <?php
                                echo number_format($estatisticas['media_horas'], 1, ',', '.');
                                ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="jogo__dados">
                    <img src="assets/iconMediaNota.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Nota Média:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php if ($estatisticas['media_nota'] !== null): ?>
                                <?php
                                echo number_format($estatisticas['media_nota'], 1, ',', '.');
                                ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="jogo__dados">
                    <img src="assets/iconFavorito.png" alt="" class="jogo__imagem">
                    <div class="jogo__texto">
                        <h3 class="jogos__estatisticas__titulo">Total de Jogos Favoritos:</h3>
                        <p class="jogos__estatisticas__texto">
                            <?php
                            echo number_format($estatisticas['total_favorito'], 0, ',', '.');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="detalhes__acoes">
                <a href="index.php" class="jogos__link">Voltar</a>
            </div>
        </div>
    </main>

</body>

</html>