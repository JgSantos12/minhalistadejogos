<?php
require __DIR__ . '/cliente.php';

$erro = null;
$aviso = pegar_aviso();

// 1) Trata as ações enviadas pelos formulários (HTML só envia GET ou POST;
//    é o PHP que converte a ação em POST, PUT ou DELETE para a API)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $acao = $_POST['acao'] ?? '';
  $id = (int) ($_POST['id'] ?? 0);

  if ($acao === 'excluir') {
    $r = chamar_api('DELETE', $id);
  } else {
    $r = ['status' => 400, 'dados' => ['erro' => 'Ação inválida']];
  }

  if ($r['status'] >= 200 && $r['status'] < 300) {
    definir_aviso('Jogo excluído com sucesso!');
    header('Location: index.php');
    exit;
  }

  $erro = $r['dados']['erro'] ?? 'Erro desconhecido';
}



// 2) Busca a lista na API para exibir
$consulta = [];
foreach (['status', 'buscar', 'ordem'] as $campo) {
  if (isset($_GET[$campo]) && is_string($_GET[$campo])) {
    $valor = trim($_GET[$campo]);
    if ($valor !== '') {
      $consulta[$campo] = $valor;
    }
  }
}

$r = chamar_api('GET', null, null, $consulta);
$jogos = $r['status'] === 200 ? $r['dados'] : [];

if ($r['status'] !== 200) {
  $erro = $r['dados']['erro'] ?? 'Falha ao listar os jogos';
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

<body>
  <main>
    <header>
      <h2 class="header__titulo">Minha Lista de Jogos</h2>
      <?php if ($aviso): ?><p class="sucesso"><?= h($aviso) ?></p><?php endif; ?>
    </header>

    <div class="banner">
      <div class="banner__texto">
        <h3 class="banner__titulo">Bem vindo a Minha Lista de Jogos!</h3>
        <p class="banner__descricao">Gerencie sua coleção de jogos favoritos!</p>
      </div>
      <div class="banner__links">
        <a href="adicionar.php" class="jogos__link">Adicionar Jogo</a>
        <a href="estatisticas.php" class="jogos__link jogos__link__b">Veja suas Estatísticas</a>
      </div>
    </div>

    <h2 class="cabecalho__titulo">Meus Jogos</h2>

    <div class="jogos__div">
      <aside>
        <div class="jogos__busca">
          <!-- Formulário de busca -->
          <form method="get" class="buscar">
            <input type="text" name="buscar" placeholder="Buscar por nome..." required>
            <?php if (isset($consulta['status'])): ?>
              <input type="hidden" name="status" value="<?= h($consulta['status']) ?>">
            <?php endif; ?>
            <button type="submit">Buscar</button>
          </form>
          <!-- Botões de ordenação -->
          <div class="jogos__ordenacao">
            <a href="<?= h(url_com($consulta, ['ordem' => 'nome'])) ?>"
              class="jogos__link jogos__link__menor <?= ($consulta['ordem'] ?? '') === 'nome' ? 'ativo__botao' : '' ?>">Ordenar
              por nome</a>
            <a href="<?= h(url_com($consulta, ['ordem' => 'nota'])) ?>"
              class="jogos__link jogos__link__menor <?= ($consulta['ordem'] ?? '') === 'nota' ? 'ativo__botao' : '' ?>">Ordenar
              por nota</a>
          </div>
        </div>
        <div class="jogos__filtros">
          <h3 class="filtros__titulo">Filtros</h3>
          <!-- Botões de status -->
          <a href="index.php" class="filtros__link <?= !isset($consulta['status']) ? 'ativo' : '' ?>">Todos os Jogos</a>

          <a href="<?= h(url_com($consulta, ['status' => 'Jogando'], ['buscar'])) ?>"
            class="filtros__link <?= ($consulta['status'] ?? '') === 'Jogando' ? 'ativo' : '' ?>">Jogando</a>

          <a href="<?= h(url_com($consulta, ['status' => 'Planejo Jogar'], ['buscar'])) ?>"
            class="filtros__link <?= ($consulta['status'] ?? '') === 'Planejo Jogar' ? 'ativo' : '' ?>">Planejo
            Jogar</a>

          <a href="<?= h(url_com($consulta, ['status' => 'Zerado'], ['buscar'])) ?>"
            class="filtros__link <?= ($consulta['status'] ?? '') === 'Zerado' ? 'ativo' : '' ?>">Zerado</a>

          <a href="<?= h(url_com($consulta, ['status' => 'Abandonado'], ['buscar'])) ?>"
            class="filtros__link <?= ($consulta['status'] ?? '') === 'Abandonado' ? 'ativo' : '' ?>">Abandonado</a>
        </div>
      </aside>

      <?php if (empty($jogos)): ?>
        <div class="index__erro">
          <p>Nenhum jogo cadastrado</p>
          <a href="adicionar.php" class="jogos__link">Cadastre Agora</a>
        </div>
      <?php endif; ?>

      <div class="div__jogos__corpo">
        <!-- Lista de jogos -->
        <?php foreach ($jogos as $jogo): ?>
          <div class="jogos__card">
            <p class="imagem__card">🎮</p>
            <strong class="jogo__nome">
              <?= h($jogo['nome']) ?>
            </strong>
            <p class="plataformas">
              <?= h(implode(', ', $jogo['plataformas'])); ?>
            </p>
            <?php if ($jogo['nota'] !== null): ?>
              <?php if ($jogo['nota'] === 10): ?>
                <p class="nota zerado">
                  <?= h($jogo['nota']) ?>
                </p>
              <?php elseif ($jogo['nota'] < 10 && $jogo['nota'] >= 7): ?>
                <p class="nota jogando">
                  <?= h($jogo['nota']) ?>
                </p>
              <?php elseif ($jogo['nota'] < 7 && $jogo['nota'] >= 4): ?>
                <p class="nota cinza">
                  <?= h($jogo['nota']) ?>
                </p>
              <?php elseif ($jogo['nota'] < 4): ?>
                <p class="nota abandonado">
                  <?= h($jogo['nota']) ?>
                </p>
              <?php endif; ?>
            <?php else: ?>
              <p class="nota cinza">
                -
              </p>
            <?php endif; ?>

            <?php if ($jogo['status'] === 'Planejo Jogar'): ?>
              <p class="status">
                <?= h($jogo['status']) ?>
              </p>
            <?php elseif ($jogo['status'] === 'Abandonado'): ?>
              <p class="status abandonado">
                <?= h($jogo['status']) ?>
              </p>
            <?php elseif ($jogo['status'] === 'Jogando'): ?>
              <p class="status jogando">
                <?= h($jogo['status']) ?>
              </p>
            <?php elseif ($jogo['status'] === 'Zerado'): ?>
              <p class="status zerado">
                <?= h($jogo['status']) ?>
              </p>
            <?php endif; ?>

            <a href="detalhes.php?id=<?= (int) $jogo['id_jogo'] ?>" class="card__link">Detalhes</a>
            <div class="card__acoes">
              <a href="editar.php?id=<?= (int) $jogo['id_jogo'] ?>" class="card__link editar">Editar</a>
              <form method="post">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id" value="<?= (int) $jogo['id_jogo'] ?>">
                <button type="submit" class="excluir">Excluir</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>
</body>

</html>