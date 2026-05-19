<?php

$isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/src/Views');

require_once ROOT_PATH . '/src/Core/Helpers.php';

if (file_exists(ROOT_PATH . '/.env')) {
    carregar_env(ROOT_PATH . '/.env');
}

configurar_fuso_horario();

$appUrl = trim($_ENV['APP_URL'] ?? '');
$hostHeader = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostSeguro = preg_match('/^[a-z0-9\.\-]+(?::[0-9]+)?$/i', $hostHeader) ? $hostHeader : 'localhost';
$scheme = $isHttps ? 'https' : 'http';

define('BASE_URL', $appUrl !== '' ? rtrim($appUrl, '/') : ($scheme . '://' . $hostSeguro));

$ambiente = $_ENV['APP_ENV'] ?? 'production';

if ($ambiente === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
}

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file(__DIR__ . $path)) {
        return false;
    }
}

require_once ROOT_PATH . '/src/Core/Router.php';
require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Core/Model.php';

$dbPath = $_ENV['DB_PATH'] ?? null;

if (!$dbPath) {
    error_log("CRÍTICO: a variável DB_PATH não foi definida no arquivo .env.");

    header("HTTP/1.1 500 Internal Server Error");
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 10%; color: #334155;'>";
    echo "<h1>Erro Interno do Servidor</h1>";
    echo "<p>O sistema está temporariamente indisponível. Por favor, tente novamente mais tarde.</p>";
    echo "</div>";
    exit;
}

putenv("DB_PATH=$dbPath");

$url = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = str_replace('/index.php', '', $url);
$url = trim($url, '/');

if ($url === '') {
    $url = 'login';
}

$url = filter_var($url, FILTER_SANITIZE_URL);

$rotasPublicas = ['login', 'recuperar'];
$prefixoPublico = explode('/', $url)[0] ?? 'login';

if (isset($_SESSION['usuario_id']) && !in_array($prefixoPublico, $rotasPublicas, true)) {
    $timeoutSegundos = 1800;
    $ultimaAtividade = $_SESSION['last_activity'] ?? time();

    if ((time() - $ultimaAtividade) > $timeoutSegundos) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        session_start();
        $_SESSION['mensagem_erro'] = 'Sua sessão expirou por inatividade. Faça login novamente.';
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

$router = new Router();
$router->dispatch($url);
