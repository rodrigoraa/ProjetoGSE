<?php
require_once ROOT_PATH . '/src/Core/Database.php';
class Model
{
    protected static $pdo;
    public static function getConexao()
    {
        if (!self::$pdo) {
            self::$pdo = \src\Core\Database::getConnection();
        }
        return self::$pdo;
    }

    public function __construct()
    {
        if (!self::$pdo) {
            $dbFile = getenv('DB_PATH');

            if (!$dbFile || !file_exists($dbFile)) {
                error_log("CRÍTICO (Model): Banco de dados não encontrado. Caminho tentado: " . ($dbFile ?: 'VAZIO'));

                self::mostrarErroGenerico();
            }

            try {
                self::$pdo = new PDO("sqlite:" . $dbFile);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                self::$pdo->exec('PRAGMA foreign_keys = ON;');
            } catch (PDOException $e) {
                error_log("CRÍTICO (PDO): Erro de conexão com o banco - " . $e->getMessage());

                self::mostrarErroGenerico();
            }
        }
    }

    private static function mostrarErroGenerico()
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 10%; color: #334155;'>";
        echo "<h1>Serviço Indisponível</h1>";
        echo "<p>Não foi possível acessar a base de dados neste momento. Por favor, tente novamente mais tarde.</p>";
        echo "</div>";
        exit;
    }
}
