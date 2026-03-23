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
    if (!isset($_SESSION['csrf_token']) || $token_post !== $_SESSION['csrf_token']) {
        die("<h1>Erro de Segurança (CSRF)</h1><p>O token de validação expirou ou é inválido. Tente recarregar a página.</p>");
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
            $valor = trim($valor);

            $_ENV[$nome] = $valor;
            putenv("$nome=$valor");
        }
    }
    return true;
}
