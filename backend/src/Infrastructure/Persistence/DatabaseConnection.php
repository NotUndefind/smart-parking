<?php

namespace Infrastructure\Persistence;

use PDO;
use PDOException;

class DatabaseConnection
{
    private static ?PDO $instance = null;
    public static function getInstance(array $config): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    '%s:host=%s;dbname=%s;charset=utf8mb4',
                    $config['driver'] ?? 'mysql',
                    $config['host'] ?? 'localhost',
                    $config['database']
                );

                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException(
                    'Erreur de connexion à la base de données: ' . $e->getMessage()
                );
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}


