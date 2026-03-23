<?php

class Model
{
    protected static $pdo;

    public function __construct()
    {
        if (!self::$pdo) {
            $dbFile = getenv('DB_PATH');

            if (!$dbFile || !file_exists($dbFile)) {
                die("ERRO CRÍTICO: O arquivo do banco de dados não foi encontrado ou o caminho no .env está incorreto. Caminho tentado: " . ($dbFile ?: 'VAZIO'));
            }

            try {
                self::$pdo = new PDO("sqlite:" . $dbFile);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                self::$pdo->exec('PRAGMA foreign_keys = ON;');
            } catch (PDOException $e) {
                die("ERRO DE CONEXÃO: " . $e->getMessage());
            }
        }
    }

    public static function getConexao()
    {
        if (!self::$pdo) {
            new self();
        }

        return self::$pdo;
    }
}