<?php
require __DIR__ . '/cliente.php';

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$erro = null;
$t = [];

// Buscar as opções de plataformas, status e gêneros para exibir no formulário
$plataformas = chamar_api('GET', null, null, ['plataformas' => 1]);
$generos = chamar_api('GET', null, null, ['generos' => 1]);
$status = ['Planejo Jogar', 'Jogando', 'Zerado', 'Abandonado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $dados = [
    'nome' => $_POST['nome'] ?? '',
    'genero' => $_POST['genero'] ?? '',
    'status' => $_POST['status'] ?? '',
    'nota' => ($_POST['nota'] !== '') ? (int) $_POST['nota'] : null,
    'horas_jogadas' => ($_POST['horas_jogadas'] !== '') ? (int) $_POST['horas_jogadas'] : 0,
    'descricao' => $_POST['descricao'] ?? '',
    'data_inicio' => $_POST['data_inicio'] ?? '',
    'data_fim' => $_POST['data_fim'] ?? '',
    'plataformas' => array_map('intval', $_POST['plataformas'] ?? []),
    'favorito' => isset($_POST['favorito']) ? (int) $_POST['favorito'] : 0
  ];

  // Envia os dados do formulário para a API (PUT)
  $r = chamar_api('PUT', $id, $dados);

  if ($r['status'] === 200) {
    definir_aviso('Jogo atualizado com sucesso!');
    header('Location: index.php');
    exit;
  }

  $erro = $r['dados']['erro'] ?? 'Erro ao salvar';

  $t = $_POST;                                      // mantém o que foi digitado
} else {
  if ($id === 0) {
    $erro = 'ID do jogo não informado';
  } else {
    // Carrega o jogo na API (GET ?id=)
    $r = chamar_api('GET', $id);
    if ($r['status'] === 200) {
      $t = $r['dados'];
    } else {
      $erro = $r['dados']['erro'] ?? 'Não foi possível carregar o jogo';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar jogo</title>
  <link rel="stylesheet" href="style.css">
</head>

<body class="adicionar">
  <main class="card__adicionar card__editar">
    <div class="adicionar__conteudo">
      <h2 class="cabecalho__titulo adicionar__titulo">Editar jogo</h2>

      <?php if ($erro): ?>
        <div class="detalhes__mensagem-erro">
          <p class="erro">⚠️
            <?= h($erro) ?>
          </p>
          <a class="jogos__link" href="index.php">Voltar</a>
        </div>
      <?php endif; ?>

      <?php if ($t): ?>
        <form method="post">
          <div class="adicionar__container">
            <input type="hidden" name="id" value="<?= $id ?>">

            <label class="adicionar__data__grupo">Nome:
              <input type="text" name="nome" value="<?= h($t['nome'] ?? '') ?>" maxlength="150" required
                class="adicionar__nome">
            </label>

            <label class="adicionar__data__grupo">Descrição:
              <textarea name="descricao" rows="4" class="adicionar__descricao"><?= h($t['descricao'] ?? '') ?></textarea>
            </label>

            <div class="adicionar__selects">
              <label class="adicionar__data__grupo">Gênero:
                <select name="genero" required class="adicionar__select">
                  <option value="">Selecione o gênero</option>
                  <?php if ($generos['status'] === 200): ?>
                    <?php foreach ($generos['dados'] as $g): ?>
                      <option value="<?= h($g) ?>" <?php echo ($t['genero'] ?? '') === $g ? 'selected' : ''; ?>><?= h($g) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </label>
              <label class="adicionar__data__grupo">Status:
                <select name="status" id="status" required class="adicionar__select">
                  <option value="">Selecione o status</option>
                  <?php foreach ($status as $s): ?>
                    <option value="<?= h($s) ?>" <?php echo ($t['status'] ?? '') === $s ? 'selected' : ''; ?>><?= h($s) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>
            <div class="adicionar__grupo">
              <label>Nota:
                <input type="number" name="nota" id="nota" placeholder="Nota (0-10)" min="0" max="10"
                  value="<?php echo h($t['nota'] ?? ''); ?>" class="adicionar__nome adicionar__nota">
              </label>
              <label>Horas Jogadas:
                <input type="number" name="horas_jogadas" id="horas_jogadas" placeholder="Horas Jogadas" min="0"
                  value="<?php echo h($t['horas_jogadas'] ?? ''); ?>" class="adicionar__nome">
              </label>
            </div>
            <label class="adicionar__data__grupo">Data de Início:
              <input type="date" name="data_inicio" id="data_inicio" value="<?php echo h($t['data_inicio'] ?? ''); ?>"
                class="adicionar__nome">
            </label>
            <label class="adicionar__data__grupo">Data de Fim:
              <input type="date" name="data_fim" id="data_fim" value="<?php echo h($t['data_fim'] ?? ''); ?>"
                class="adicionar__nome">
            </label>
            <fieldset>
              <legend>Plataformas</legend>
              <?php foreach ($plataformas['dados'] as $p): ?>
                <label> <?= h($p['nome']) ?>
                  <input type="checkbox" name="plataformas[]" value="<?= h($p['id_plataforma']) ?>" <?php echo in_array($p['nome'], $t['plataformas'] ?? []) ? 'checked' : ''; ?>>
                </label>
              <?php endforeach; ?>
            </fieldset>
            <label class="favorito">
              <input type="hidden" name="favorito" value="0">
              <input type="checkbox" name="favorito" value="1" <?php echo ($t['favorito'] == 1) ? 'checked' : 0; ?>>
              <p>Adicionar aos Favoritos:</p>
              <span class="icone-estrela">★</span>
            </label>
          </div>
          <div class="adicionar__acoes">
            <button type="submit" class="jogos__link" style="border: none;">Salvar</button>
            <a href="index.php" class="jogos__link jogos__link__b">Voltar</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
    <div class="adicionar__banner editar__banner">

    </div>
  </main>
</body>

</html>