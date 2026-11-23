<?php
require __DIR__ . '/cliente.php';

$erro = null;

// Buscar as opções de plataformas, status e gêneros para exibir no formulário
$plataformas = chamar_api('GET', null, null, ['plataformas' => 1]);
$generos = chamar_api('GET', null, null, ['generos' => 1]);
$status = ['Planejo Jogar', 'Jogando', 'Zerado', 'Abandonado'];

// 1) Trata as ações enviadas pelos formulários (HTML só envia GET ou POST;
//    é o PHP que converte a ação em POST, PUT ou DELETE para a API)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'genero' => $_POST['genero'] ?? '',
        'status' => $_POST['status'] ?? '',
        'nota' => ($_POST['nota'] !== '') ? (int) $_POST['nota'] : null,
        'horas_jogadas' => ($_POST['horas_jogadas'] !== '') ? (int) $_POST['horas_jogadas'] : 0,
        'descricao' => $_POST['descricao'] ?? '',
        'data_inicio' => $_POST['data_inicio'] ?? '',
        'plataformas' => array_map('intval', $_POST['plataformas'] ?? [])
    ];

    if ($acao === 'adicionar') {
        $r = chamar_api('POST', null, $dados);
    } else {
        $r = ['status' => 400, 'dados' => ['erro' => 'Ação inválida']];
    }

    if ($r['status'] >= 200 && $r['status'] < 300) {
        definir_aviso('Jogo adicionado com sucesso!');
        header('Location: index.php');
        exit;
    } else {
        $erro = $r['dados']['erro'] ?? 'Falha ao adicionar o jogo';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minha Lista de Jogos</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="adicionar">
    <main class="card__adicionar">
        <div class="adicionar__banner">

        </div>
        <div class="adicionar__conteudo">
            <h2 class="cabecalho__titulo adicionar__titulo">Adicionar Jogo</h2>
            <?php if ($erro): ?>
                <div class="detalhes__mensagem-erro">
                    <p class="erro">⚠️
                        <?= h($erro) ?>
                    </p>
                    <a class="jogos__link" href="index.php">Voltar</a>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="adicionar__container">
                    <input type="text" name="nome" id="nome" placeholder="Nome do jogo" maxlength="60" required
                        value="<?php echo h($_POST['nome'] ?? ''); ?>" class="adicionar__nome">

                    <textarea name="descricao" id="descricao" placeholder="Descrição do jogo"
                        class="adicionar__descricao"><?php echo h($_POST['descricao'] ?? ''); ?></textarea>

                    <div class="adicionar__selects">
                        <select name="genero" id="genero" required class="adicionar__select">
                            <option value="">Selecione o gênero</option>
                            <?php foreach ($generos['dados'] as $g): ?>
                                <option value="<?= h($g) ?>" <?php echo ($_POST['genero'] ?? '') === $g ? 'selected' : ''; ?>>
                                    <?= h($g) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status" id="status" required class="adicionar__select">
                            <option value="">Selecione o status</option>
                            <?php foreach ($status as $s): ?>
                                <option value="<?= h($s) ?>" <?php echo ($_POST['status'] ?? '') === $s ? 'selected' : ''; ?>>
                                    <?= h($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="adicionar__grupo">
                        <input type="number" name="nota" id="nota" placeholder="Nota (0-10)" min="0" max="10"
                            value="<?php echo h($_POST['nota'] ?? ''); ?>" class="adicionar__nome adicionar__nota">

                        <input type="number" name="horas_jogadas" id="horas_jogadas" placeholder="Horas Jogadas" min="0"
                            value="<?php echo h($_POST['horas_jogadas'] ?? ''); ?>" class="adicionar__nome">
                    </div>

                    <div class="adicionar__data__grupo">
                        <p>Data de Início:</p>
                        <input type="date" name="data_inicio" id="data_inicio" placeholder="Data de Início"
                            value="<?php echo h($_POST['data_inicio'] ?? ''); ?>" class="adicionar__nome">
                    </div>

                    <fieldset>
                        <legend>Plataformas</legend>
                        <?php foreach ($plataformas['dados'] as $p): ?>
                            <label class="adicionar__plataformas">
                                <input type="checkbox" name="plataformas[]" value="<?= h($p['id_plataforma']) ?>" <?php echo in_array($p['id_plataforma'], $_POST['plataformas'] ?? []) ? 'checked' : ''; ?>>
                                <label for="<?= h($p['id_plataforma']) ?>"><?= h($p['nome']) ?></label>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                </div>
                <div class="adicionar__acoes">
                    <input type="hidden" name="acao" value="adicionar">
                    <button type="submit" class="jogos__link" style="border: none;">Adicionar Jogo</button>
                    <a href="index.php" class="jogos__link jogos__link__b">Voltar</a>
                </div>

            </form>
        </div>
    </main>
</body>

</html>