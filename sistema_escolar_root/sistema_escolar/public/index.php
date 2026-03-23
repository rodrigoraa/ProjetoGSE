<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file(__DIR__ . $path)) {
        return false;
    }
}

define('ROOT_PATH', dirname(__DIR__));
define('VIEW_PATH', ROOT_PATH . '/src/Views');
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

require_once ROOT_PATH . '/src/Core/Helpers.php';
require_once ROOT_PATH . '/src/Core/Router.php';
require_once ROOT_PATH . '/src/Core/Controller.php';
require_once ROOT_PATH . '/src/Core/Model.php';

if (file_exists(ROOT_PATH . '/.env')) {
    carregar_env(ROOT_PATH . '/.env');
}

$dbPath = $_ENV['DB_PATH'] ?? null;

if (!$dbPath) {
    die("Erro Crítico: A variável DB_PATH não foi definida no arquivo .env.");
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
