<?php
session_start();
// CLIENTE DA API (usado pelo frontend PHP)
// O navegador nunca fala com a API: quem chama é o PHP, no servidor, usando cURL.

const API_URL = 'http://localhost/jogos/api/tarefas.php';

/**
 * Faz uma requisição HTTP para a API.
 * Retorna ['status' => código HTTP, 'dados' => array decodificado do JSON]
 */
function chamar_api(string $metodo, ?int $id = null, ?array $dados = null, array $consulta = []): array
{
    $url = API_URL;

    if ($id) {
        $url .= '?id=' . $id;
    }

    if ($consulta) {
        $url .= ($id ? '&' : '?') . http_build_query($consulta);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $metodo,            // GET, POST, PUT ou DELETE
        CURLOPT_RETURNTRANSFER => true,               // devolve o corpo como string
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 5,
    ]);
    if ($dados !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
    }

    $corpo = curl_exec($ch);
    if ($corpo === false) {                           // API fora do ar / URL errada
        return ['status' => 0, 'dados' => ['erro' => 'Não foi possível acessar a API: ' . curl_error($ch)]];
    }

    return [
        'status' => curl_getinfo($ch, CURLINFO_RESPONSE_CODE),
        'dados'  => json_decode($corpo, true) ?? [],
    ];
}

// Escapa texto antes de exibir em HTML (proteção contra XSS)
function h(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function url_com(array $consulta, array $mudancas = [], array $remover = []): string
{
    $params = array_merge($consulta, $mudancas);
    foreach ($remover as $chave) {
        unset($params[$chave]);
    }
    return $params ? '?' . http_build_query($params) : 'index.php';
}

function definir_aviso(string $texto): void
{
    $_SESSION['aviso'] = $texto;
}

function pegar_aviso(): ?string
{
    $texto = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);
    return $texto;
}
