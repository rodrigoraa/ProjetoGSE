<?php

namespace src\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {

            $dbPath = $_ENV['DB_PATH'] ?? null;

            if (!$dbPath || !file_exists($dbPath)) {
                error_log("CRÍTICO (Database): Caminho do banco não configurado ou arquivo não encontrado. Caminho: " . ($dbPath ?: 'NULO'));

                self::mostrarErroGenerico();
            }

            try {
                self::$instance = new PDO("sqlite:$dbPath");

                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                self::$instance->exec('PRAGMA foreign_keys = ON;');
            } catch (PDOException $e) {
                error_log("CRÍTICO (Database): Erro técnico ao conectar no banco - " . $e->getMessage());

                self::mostrarErroGenerico();
            }
        }

        return self::$instance;
    }

    private static function mostrarErroGenerico(): void
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 10%; color: #334155;'>";
        echo "<h1>Serviço Temporariamente Indisponível</h1>";
        echo "<p>Não foi possível estabelecer conexão com a base de dados. Nossa equipe técnica já foi notificada. Por favor, tente novamente em instantes.</p>";
        echo "</div>";
        exit;
    }
}
