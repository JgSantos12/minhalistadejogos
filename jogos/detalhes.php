<?php
require __DIR__ . '/cliente.php';

$erro = null;
$jogo = [];

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $r = chamar_api('GET', $id);
    if ($r['status'] === 200) {
        $jogo = $r['dados'];
    } else {
        $erro = $r['dados']['erro'] ?? 'Não foi possível carregar o jogo';
    }
} else {
    $erro = 'ID do jogo não fornecido.';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Jogo</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="adicionar">
    <main class="card__detalhes">
        <div class="banner detalhes__banner">

        </div>
        <div class="detalhes__container">
            <h2 class="cabecalho__titulo">Detalhes do Jogo</h2>

            <?php if ($erro): ?>
                <div class="detalhes__mensagem-erro">
                    <p class="erro">⚠️
                        <?= h($erro) ?>
                    </p>
                    <a class="jogos__link" href="index.php">Voltar</a>
                </div>
            <?php endif; ?>

            <?php if ($jogo): ?>
                <div class="detalhes__dados">
                    <div class="jogo__dados">
                        <img src="assets/iconNome.png" alt="" class="jogo__imagem ">
                        <div class="jogo__texto">
                            <p class="jogo__label">Nome:</p>
                            <p><?= h($jogo['nome'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconDescricao.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Descrição:</p>
                            <?php if ($jogo['descricao'] === null): ?>
                                <p>Sem Descrição.</p>
                            <?php else: ?>
                                <p><?= nl2br(h($jogo['descricao'] ?? '')) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconGenero.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Gênero:</p>
                            <p><?= h($jogo['genero'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconStatus.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Status:</p>
                            <p><?= h($jogo['status'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconNota.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Nota:</p>
                            <?php if ($jogo['nota'] === null): ?>
                                <p>-</p>
                            <?php else: ?>
                                <p><?= h($jogo['nota'] ?? '') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconHoras.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Horas Jogadas:</p>
                            <p><?= h($jogo['horas_jogadas'] ?? '') ?></p>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconDataInicio.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Data de Início:</p>
                            <p><?= date('d/m/Y', strtotime(h($jogo['data_inicio'] ?? ''))) ?></p>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconDataFim.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Data de Término:</p>
                            <?php if ($jogo['data_fim'] === null): ?>
                                <p>-</p>
                            <?php else: ?>
                                <p><?= date('d/m/Y', strtotime(h($jogo['data_fim'] ?? ''))) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconFavorito.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Favorito:</p>
                            <?php if ($jogo['favorito'] === 0): ?>
                                <p>Não</p>
                            <?php else: ?>
                                <p>Sim</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="jogo__dados">
                        <img src="assets/iconPlataformas.png" alt="" class="jogo__imagem">
                        <div class="jogo__texto">
                            <p class="jogo__label">Plataformas:</p>
                            <p><?= h(implode(', ', $jogo['plataformas'] ?? '')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="detalhes__acoes">
                    <a href="editar.php?id=<?= (int) $jogo['id_jogo'] ?>" class="jogos__link">Editar</a>
                    <a class="jogos__link jogos__link__b" href="index.php">Voltar</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>