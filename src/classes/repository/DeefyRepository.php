<?php

declare(strict_types=1);

namespace iutnc\deefy\repository;

class DeefyRepository {
    private \PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf) {
        $this->pdo = new \PDO(
            $conf['dsn'],
            $conf['user'],
            $conf['pass'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_STRINGIFY_FETCHES => false
            ]
        );
        $this->pdo->prepare("SET NAMES 'UTF8'")->execute();
    }

    public static function getInstance(): DeefyRepository {
        if (is_null(self::$instance)) {
            if (empty(self::$config)) {
                throw new \Exception("Configuration base de données non initialisée. Appelez DeefyRepository::setConfig() au préalable.");
            }
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file): void {
        if (!file_exists($file)) {
            throw new \Exception("Fichier de configuration introuvable : $file");
        }
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Erreur lors de la lecture du fichier de configuration : $file");
        }

        $driver = $conf['driver'] ?? 'mysql';
        $host = $conf['host'] ?? 'localhost';
        $database = $conf['database'] ?? 'deefy';
        $port = isset($conf['port']) ? ";port={$conf['port']}" : "";

        $dsn = "{$driver}:host={$host}{$port};dbname={$database};charset=utf8";

        self::$config = [
            'dsn' => $dsn,
            'user' => $conf['username'] ?? '',
            'pass' => $conf['password'] ?? ''
        ];
    }

    public function getPdo(): \PDO {
        return $this->pdo;
    }
}
