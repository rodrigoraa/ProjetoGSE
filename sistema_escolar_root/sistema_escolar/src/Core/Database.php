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
                die("ERRO CRÍTICO: Caminho do banco de dados não configurado no .env ou arquivo não encontrado. Caminho: " . ($dbPath ?: 'NULO'));
            }

            try {
                self::$instance = new PDO("sqlite:$dbPath");
                
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                
            } catch (PDOException $e) {
                die("Erro técnico ao conectar no banco: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}