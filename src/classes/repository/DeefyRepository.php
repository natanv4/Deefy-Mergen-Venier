<?php

declare(strict_types=1);

namespace iutnc\deefy\repository;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;

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
                throw new \Exception("Configuration base de donnees non initialisee. Appelez DeefyRepository::setConfig() au prealable.");
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

    public function saveEmptyPlaylist(Playlist $pl): Playlist {
        $query = "INSERT INTO playlist (nom) VALUES (:nom)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $pl->nom]);
        $pl->setId((int)$this->pdo->lastInsertId());
        return $pl;
    }

    public function saveTrack(AudioTrack $track, int $playlistId): AudioTrack {
        $query = "INSERT INTO track (titre, genre, duree, filename, artiste_auteur) 
                  VALUES (:titre, :genre, :duree, :filename, :artiste_auteur)";
        $stmt = $this->pdo->prepare($query);

        $artisteAuteur = null;
        if ($track instanceof AlbumTrack) {
            $artisteAuteur = $track->artiste;
        } elseif ($track instanceof PodcastTrack) {
            $artisteAuteur = $track->auteur;
        }

        $stmt->execute([
            'titre' => $track->titre,
            'genre' => $track->genre,
            'duree' => $track->duree,
            'filename' => $track->filename,
            'artiste_auteur' => $artisteAuteur
        ]);

        $trackId = (int)$this->pdo->lastInsertId();
        $track->setId($trackId);

        $qNum = "SELECT COALESCE(MAX(no_piste_dans_liste), 0) + 1 AS next_num 
                 FROM playlist2track 
                 WHERE playlist_id = :pl_id";
        $stmtNum = $this->pdo->prepare($qNum);
        $stmtNum->execute(['pl_id' => $playlistId]);
        $nextNum = (int)$stmtNum->fetchColumn();

        $qLink = "INSERT INTO playlist2track (playlist_id, track_id, no_piste_dans_liste) 
                  VALUES (:pl_id, :tr_id, :num)";
        $stmtLink = $this->pdo->prepare($qLink);
        $stmtLink->execute([
            'pl_id' => $playlistId,
            'tr_id' => $trackId,
            'num' => $nextNum
        ]);

        return $track;
    }
}
