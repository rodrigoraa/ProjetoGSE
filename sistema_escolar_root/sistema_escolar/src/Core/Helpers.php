<?php

function registrar_log($pdo, $acao, $detalhes)
{
    try {
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $usuario_nome = $_SESSION['usuario_nome'] ?? 'Sistema';

        $sql = "INSERT INTO logs (id_usuario, nome_usuario, acao, detalhes) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_nome, $acao, $detalhes]);
    } catch (Exception $e) {
        error_log("Falha ao salvar log no banco de dados: " . $e->getMessage());
    }
}

function gerar_csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_csrf_token($token_post)
{
    if (!isset($_SESSION['csrf_token']) || !is_string($token_post) || !hash_equals($_SESSION['csrf_token'], $token_post)) {
        $usuario_id = $_SESSION['usuario_id'] ?? 'Desconhecido';
        error_log("Alerta de Segurança (CSRF): Tentativa de envio com token inválido. Usuário ID: {$usuario_id}");

        $_SESSION['mensagem_erro'] = "Sua sessão expirou ou a requisição é inválida. Por favor, tente novamente.";

        redirect('/');
        exit;
    }
}

function selected($valor1, $valor2)
{
    return ($valor1 == $valor2) ? 'selected' : '';
}

function redirect($path)
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function e($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function carregar_env($caminho_arquivo)
{
    if (!file_exists($caminho_arquivo)) {
        return false;
    }

    $linhas = file($caminho_arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        if (strpos(trim($linha), '#') === 0) continue;

        if (strpos($linha, '=') !== false) {
            list($nome, $valor) = explode('=', $linha, 2);

            $nome = trim($nome);
            $valor = trim($valor, " \t\n\r\0\x0B\"");

            $_ENV[$nome] = $valor;
            putenv("$nome=$valor");
        }
    }
    return true;
}
