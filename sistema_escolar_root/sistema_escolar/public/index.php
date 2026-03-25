<?php
session_start();

define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/src/Views');
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

require_once ROOT_PATH . '/src/Core/Helpers.php';

if (file_exists(ROOT_PATH . '/.env')) {
    carregar_env(ROOT_PATH . '/.env');
}

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
    error_log("CRÍTICO: A variável DB_PATH não foi definida no arquivo .env.");
    
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

$router = new Router();
$router->dispatch($url);