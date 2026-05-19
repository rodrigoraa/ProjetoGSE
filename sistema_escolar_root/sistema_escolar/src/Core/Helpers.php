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
        error_log("Alerta de Segurança (CSRF): tentativa de envio com token inválido. Usuário ID: {$usuario_id}");

        definir_flash(
            'erro',
            'Não foi possível concluir a ação',
            'Sua sessão expirou ou a requisição enviada não é mais válida.',
            'Atualize a página e tente novamente. Se o problema continuar, saia e entre no sistema outra vez.'
        );

        redirect('/');
        exit;
    }
}

function alerta_html($tipo, $titulo, $mensagem, $ajuda = '')
{
    $tipos_permitidos = ['sucesso', 'erro', 'aviso', 'info'];
    $tipo_normalizado = in_array($tipo, $tipos_permitidos, true) ? $tipo : 'info';

    $html = '<div class="alert-box alert-' . e($tipo_normalizado) . '">';
    $html .= '<div class="alert-title">' . e($titulo) . '</div>';
    $html .= '<div class="alert-text">' . e($mensagem) . '</div>';

    if (!empty($ajuda)) {
        $html .= '<div class="alert-help">' . e($ajuda) . '</div>';
    }

    $html .= '</div>';

    return $html;
}

function definir_flash($tipo, $titulo, $mensagem, $ajuda = '')
{
    $_SESSION['flash'] = [
        'tipo' => $tipo,
        'titulo' => $titulo,
        'mensagem' => $mensagem,
        'ajuda' => $ajuda
    ];
}

function consumir_flash()
{
    if (!empty($_SESSION['flash']) && is_array($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return alerta_html(
            $flash['tipo'] ?? 'info',
            $flash['titulo'] ?? 'Aviso',
            $flash['mensagem'] ?? '',
            $flash['ajuda'] ?? ''
        );
    }

    if (!empty($_SESSION['sucesso'])) {
        $mensagem = $_SESSION['sucesso'];
        unset($_SESSION['sucesso']);
        return alerta_html('sucesso', 'Operação concluída', $mensagem);
    }

    if (!empty($_SESSION['mensagem_erro'])) {
        $mensagem = $_SESSION['mensagem_erro'];
        unset($_SESSION['mensagem_erro']);
        return alerta_html('erro', 'Não foi possível concluir a ação', $mensagem);
    }

    if (!empty($_SESSION['msg_sucesso'])) {
        $mensagem = $_SESSION['msg_sucesso'];
        unset($_SESSION['msg_sucesso']);
        return alerta_html('sucesso', 'Operação concluída', $mensagem);
    }

    if (!empty($_SESSION['msg_erro'])) {
        $mensagem = $_SESSION['msg_erro'];
        unset($_SESSION['msg_erro']);
        return alerta_html('erro', 'Não foi possível concluir a ação', $mensagem);
    }

    return '';
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
        if (strpos(trim($linha), '#') === 0) {
            continue;
        }

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

function configurar_fuso_horario()
{
    $timezone = $_ENV['APP_TIMEZONE'] ?? getenv('APP_TIMEZONE') ?: 'America/Campo_Grande';

    if (!in_array($timezone, timezone_identifiers_list(), true)) {
        error_log("Fuso horario invalido em APP_TIMEZONE: {$timezone}");
        $timezone = 'America/Campo_Grande';
    }

    date_default_timezone_set($timezone);
}
