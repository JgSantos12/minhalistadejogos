<?php
date_default_timezone_set('America/Sao_Paulo');
// API REST de tarefas: GET, POST, PUT, DELETE  ->  /api/tarefas.php[?id=N]
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

function responder(mixed $dados, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function buscar_plataformas(PDO $pdo, int $id_jogo): array
{
    $st = $pdo->prepare('SELECT p.nome
        FROM plataformas AS p
        INNER JOIN jogos_plataformas AS jp ON p.id_plataforma = jp.id_plataforma
        INNER JOIN jogos AS j ON jp.id_jogo = j.id_jogo
        WHERE j.id_jogo = ?
        ORDER BY p.nome');

    $st->execute([$id_jogo]);

    return $st->fetchAll(PDO::FETCH_COLUMN);
}

function data_valida(string $data): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
}

function validar_jogo(array $entrada, PDO $pdo): array
{
    // Validação do nome do jogo
    $nome = trim($entrada['nome'] ?? '');
    if ($nome === '')
        responder(['erro' => 'O nome é obrigatório'], 422);
    if (mb_strlen($nome) > 60)
        responder(['erro' => 'O nome não pode ter mais de 60 caracteres'], 422);

    // Validação do gênero do jogo
    $genero = trim($entrada['genero'] ?? '');
    if (!in_array($genero, GENEROS_VALIDOS, true)) {
        responder(['erro' => 'O gênero deve ser um dos seguintes: ' . implode(', ', GENEROS_VALIDOS)], 422);
    }

    // Validação do status do jogo
    $status = trim($entrada['status'] ?? '');
    if (!in_array($status, STATUS_VALIDOS, true)) {
        responder(['erro' => 'O status deve ser um dos seguintes: ' . implode(', ', STATUS_VALIDOS)], 422);
    }

    // Validação da nota do jogo
    $nota = $entrada['nota'] ?? null;
    if ($nota !== null && (!is_int($nota) || $nota < 0 || $nota > 10)) {
        responder(['erro' => 'A nota deve ser um número inteiro entre 0 e 10'], 422);
    }
    ;

    // Validação das horas jogadas
    $horas_jogadas = $entrada['horas_jogadas'] ?? 0;
    if ($horas_jogadas !== 0 && (!is_int($horas_jogadas) || $horas_jogadas < 0)) {
        responder(['erro' => 'As horas jogadas devem ser um número inteiro não negativo'], 422);
    }

    // Validação da plataforma do jogo
    $plataformas = $entrada['plataformas'] ?? [];
    if (!is_array($plataformas) || count($plataformas) === 0) {
        responder(['erro' => 'Informe pelo menos uma plataforma'], 422);
    }

    $ids_validos = $pdo->query('SELECT id_plataforma FROM plataformas')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($plataformas as $id_plataforma) {
        if (!is_int($id_plataforma) || !in_array($id_plataforma, $ids_validos, true)) {
            responder(['erro' => "Plataforma inválida: $id_plataforma"], 422);
        }
    }

    // Validação data de início
    $data_inicio = $entrada['data_inicio'] ?? "";
    $hoje = date('Y-m-d');
    if ($data_inicio === "") {
        $data_inicio = $hoje;
    }
    if (!data_valida($data_inicio)) {
        responder(['erro' => 'Data de início inválida. Use o formato YYYY-MM-DD'], 422);
    }
    if ($data_inicio > $hoje) {
        responder(['erro' => 'A data de início não pode ser futura'], 422);
    }

    // Validação data de término
    $data_fim = $entrada['data_fim'] ?? null;
    if ($data_fim === "") {
        $data_fim = null;
    }
    if ($status === 'Zerado') {
        if ($data_fim !== null) {
            if (!data_valida($data_fim)) {
                responder(['erro' => 'Data de término inválida. Use o formato YYYY-MM-DD'], 422);
            }
            if ($data_fim < $data_inicio) {
                responder(['erro' => 'A data de término não pode ser anterior à data de início'], 422);
            }
            if ($data_fim > $hoje) {
                responder(['erro' => 'A data de término não pode ser futura'], 422);
            }
        }
    } elseif ($data_fim !== null) {
        responder(['erro' => 'A data de término só pode ser informada se o status for "Zerado"'], 422);
    }

    $descricao = trim($entrada['descricao'] ?? '');
    if ($descricao === '')
        $descricao = null;

    $plataformas = array_unique($plataformas);

    return [
        'nome' => $nome,
        'descricao' => $descricao,
        'genero' => $genero,
        'status' => $status,
        'nota' => $nota,
        'horas_jogadas' => $horas_jogadas,
        'data_inicio' => $data_inicio,
        'data_fim' => $data_fim,
        'plataformas' => $plataformas,
    ];
}

const GENEROS_VALIDOS = [
    'Ação',
    'Aventura',
    'RPG',
    'Estratégia',
    'Tiro',
    'Esporte e Corrida',
    'Simulação',
    'Terror',
    'Puzzle e Plataforma',
    'Outro',
];

const STATUS_VALIDOS = ['Planejo Jogar', 'Jogando', 'Zerado', 'Abandonado'];

$metodo = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$entrada = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $pdo = conectar();

    switch ($metodo) {
        case 'GET':
            // Busca por id
            if ($id) {
                $st = $pdo->prepare('SELECT * FROM jogos WHERE id_jogo = ?');
                $st->execute([$id]);
                $jogo = $st->fetch();
                if (!$jogo)
                    responder(['erro' => 'Jogo não encontrado'], 404);
                $jogo['plataformas'] = buscar_plataformas($pdo, $jogo['id_jogo']);
                responder($jogo);
            }
            // Endpoints de apoio
            if (isset($_GET['plataformas'])) {
                $plataformas = $pdo->query('SELECT * FROM plataformas ORDER BY nome')->fetchAll();
                responder($plataformas);
            }

            if (isset($_GET['generos'])) {
                responder(GENEROS_VALIDOS);
            }

            if (isset($_GET['estatisticas'])) {
                $est =
                    $pdo->query(
                        "SELECT
                            COUNT(*) as total_jogos,
                            SUM(status = 'Zerado') as jogos_zerados, 
                            SUM(horas_jogadas) as total_horas, 
                            AVG(horas_jogadas) as media_horas, 
                            AVG(nota) as media_nota, 
                            SUM(favorito) as total_favorito 
                        FROM jogos"
                    )->fetch(PDO::FETCH_ASSOC);

                if ($est) {
                    $est['total_jogos'] = (int) $est['total_jogos'];
                    $est['jogos_zerados'] = (int) $est['jogos_zerados'];
                    $est['total_horas'] = (int) $est['total_horas'];
                    $est['media_horas'] = isset($est['media_horas']) ? round((float) $est['media_horas'], 2) : null;
                    $est['media_nota'] = isset($est['media_nota']) ? round((float) $est['media_nota'], 2) : null;
                    $est['total_favorito'] = (int) $est['total_favorito'];
                }

                responder($est);
            }

            // Busca com filtros e ordenação
            $condicoes = [];
            $valores = [];
            $ordens = [
                'nome' => 'nome ASC',
                'nota' => 'nota DESC, nome ASC',
            ];

            if (isset($_GET['status'])) {
                $status = trim($_GET['status']);
                if (!in_array($status, STATUS_VALIDOS, true)) {
                    responder(['erro' => 'Status inválido. Use um dos seguintes: ' . implode(', ', STATUS_VALIDOS)], 422);
                } else {
                    $condicoes[] = 'status = ?';
                    $valores[] = $status;
                }
            }

            if (isset($_GET['buscar'])) {
                $buscar = trim($_GET['buscar']);
                if ($buscar === '') {
                    responder(['erro' => 'O parâmetro buscar não pode estar vazio'], 422);
                } else {
                    $condicoes[] = 'nome LIKE ?';
                    $valores[] = '%' . $buscar . '%';
                }
            }

            $sql = 'SELECT * FROM jogos';
            if ($condicoes) {
                $sql .= ' WHERE ' . implode(' AND ', $condicoes);
            }

            if (isset($_GET['ordem'])) {
                $ordem = trim($_GET['ordem']);
                if (!array_key_exists($ordem, $ordens)) {
                    responder(['erro' => 'Ordem inválida. Use um dos seguintes: ' . implode(', ', array_keys($ordens))], 422);
                } else {
                    $sql .= ' ORDER BY ' . $ordens[$ordem];
                }
            } else {
                $sql .= ' ORDER BY nome ASC';
            }

            $st = $pdo->prepare($sql);
            $st->execute($valores);
            $jogos = $st->fetchAll();

            foreach ($jogos as &$jogo) {
                $jogo['plataformas'] = buscar_plataformas($pdo, $jogo['id_jogo']);
            }
            unset($jogo);
            responder($jogos);

        case 'POST': // CREATE
            $dados = validar_jogo($entrada, $pdo);

            if ($dados['status'] === 'Zerado' && $dados['data_fim'] === null) {
                $dados['data_fim'] = date('Y-m-d');
            }

            // INSERT
            $pdo->beginTransaction();

            $st = $pdo->prepare(
                'INSERT INTO jogos (nome, genero, status, nota, horas_jogadas, data_inicio, data_fim, descricao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $st->execute([
                $dados['nome'],
                $dados['genero'],
                $dados['status'],
                $dados['nota'],
                $dados['horas_jogadas'],
                $dados['data_inicio'],
                $dados['data_fim'],
                $dados['descricao']
            ]);

            $id_jogo = (int) $pdo->lastInsertId();

            $stVinculo = $pdo->prepare(
                'INSERT INTO jogos_plataformas (id_jogo, id_plataforma) 
                VALUES (?, ?)'
            );

            foreach ($dados['plataformas'] as $id_plataforma) {
                $stVinculo->execute([$id_jogo, $id_plataforma]);
            }

            $pdo->commit();
            responder(['id' => $id_jogo, 'mensagem' => 'Jogo criado'], 201);

        case 'PUT': // UPDATE
            if (!$id)
                responder(['erro' => 'Informe o id na URL'], 400);

            $st = $pdo->prepare('SELECT * FROM jogos WHERE id_jogo = ?');
            $st->execute([$id]);
            $jogo = $st->fetch();
            if (!$jogo)
                responder(['erro' => 'Jogo não encontrado'], 404);

            $dados = validar_jogo($entrada, $pdo);

            // Validação favorito
            if (isset($entrada['favorito'])) {
                $favorito = $entrada['favorito'];
                if ($favorito !== 0 && $favorito !== 1) {
                    responder(['erro' => 'O campo favorito deve ser 0 ou 1'], 422);
                }
            } else {
                $favorito = $jogo['favorito'];
            }

            // Validação data de fim
            $statusAntigo = $jogo['status'];
            $status = $dados['status'];
            $data_fim_antiga = $jogo['data_fim'];
            $data_fim = $dados['data_fim'];
            $data_inicio = $dados['data_inicio'];

            if ($status === 'Zerado') {
                if ($data_fim === null) {
                    if ($statusAntigo === 'Zerado') {
                        if ($data_fim_antiga === null) {
                            $data_fim = date('Y-m-d');
                        } else {
                            $data_fim = $data_fim_antiga;
                        }
                    } else {
                        $data_fim = date('Y-m-d');
                    }
                }
            } else {
                $data_fim = null;
            }

            if ($data_fim !== null && $data_inicio > $data_fim) {
                responder(['erro' => 'A data de início não pode ser posterior à data de término'], 422);
            }

            $pdo->beginTransaction();

            // UPDATE da tabela jogos
            $st = $pdo->prepare(
                'UPDATE jogos 
                SET nome = ?, descricao = ?, genero = ?, status = ?, nota = ?, horas_jogadas = ?, data_inicio = ?, data_fim = ?, favorito = ?
                WHERE id_jogo = ?'
            );

            $st->execute([
                $dados['nome'],
                $dados['descricao'],
                $dados['genero'],
                $status,
                $dados['nota'],
                $dados['horas_jogadas'],
                $dados['data_inicio'],
                $data_fim,
                $favorito,
                $id
            ]);

            // DELETE dos vínculos antigos desse jogo na tabela jogos_plataformas
            $deleteVinculo = $pdo->prepare('DELETE FROM jogos_plataformas WHERE id_jogo = ?');
            $deleteVinculo->execute([$id]);

            // INSERT dos novos vínculos do jogo na tabela jogos_plataformas
            $stVinculo = $pdo->prepare(
                'INSERT INTO jogos_plataformas (id_jogo, id_plataforma) 
                VALUES (?, ?)'
            );

            foreach ($dados['plataformas'] as $id_plataforma) {
                $stVinculo->execute([$id, $id_plataforma]);
            }

            $pdo->commit();

            responder(['mensagem' => 'Jogo atualizado'], 200);

        case 'DELETE': // DELETE
            if (!$id)
                responder(['erro' => 'Informe o id na URL'], 400);

            $st = $pdo->prepare('DELETE FROM jogos WHERE id_jogo = ?');
            $st->execute([$id]);

            $st->rowCount() ? responder(['mensagem' => 'Jogo removido'], 200)
                : responder(['erro' => 'Jogo não encontrado'], 404);

        default:
            responder(['erro' => 'Método não permitido'], 405);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Em produção nunca exiba $e->getMessage() ao cliente; registre em log.
    error_log($e->getMessage());
    responder(['erro' => 'Erro interno no banco de dados'], 500);
}
