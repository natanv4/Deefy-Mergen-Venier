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

    /**
     * Sauvegarde une nouvelle playlist vide dans la table playlist (Fonctionnalité 4)
     */
    public function saveEmptyPlaylist(Playlist $pl): Playlist {
        $query = "INSERT INTO playlist (nom) VALUES (:nom)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $pl->nom]);
        $pl->setId((int)$this->pdo->lastInsertId());
        return $pl;
    }

    /**
     * Récupère la liste de toutes les playlists enregistrées en base (Fonctionnalité 1)
     * @return array Tableau de tableaux associatifs ['id' => int, 'nom' => string, 'nb_pistes' => int]
     */
    public function findAllPlaylists(): array {
        $query = "SELECT p.id, p.nom, COUNT(p2t.track_id) AS nb_pistes 
                  FROM playlist p 
                  LEFT JOIN playlist2track p2t ON p.id = p2t.playlist_id 
                  GROUP BY p.id, p.nom 
                  ORDER BY p.id DESC";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une playlist complète par son ID avec toutes ses pistes ordonnées (Fonctionnalité 2)
     */
    public function findPlaylistById(int $id): Playlist {
        $query = "SELECT id, nom FROM playlist WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            throw new \Exception("Playlist introuvable (ID: {$id})");
        }

        $playlist = new Playlist($row['nom']);
        $playlist->setId((int)$row['id']);

        // Récupération des pistes associées
        $qTracks = "SELECT t.* FROM track t 
                    JOIN playlist2track p2t ON t.id = p2t.track_id 
                    WHERE p2t.playlist_id = :id 
                    ORDER BY p2t.no_piste_dans_liste ASC";
        $stmtTracks = $this->pdo->prepare($qTracks);
        $stmtTracks->execute(['id' => $id]);

        while ($t = $stmtTracks->fetch(\PDO::FETCH_ASSOC)) {
            // Création de l'objet AlbumTrack ou PodcastTrack selon le genre/données
            if (!empty($t['genre']) && strtolower($t['genre']) === 'podcast') {
                $track = new PodcastTrack($t['titre'], $t['filename']);
                $track->auteur = $t['artiste_auteur'] ?? 'Inconnu';
            } else {
                $track = new AlbumTrack($t['titre'], $t['filename']);
                $track->artiste = $t['artiste_auteur'] ?? 'Inconnu';
            }

            $track->setId((int)$t['id']);
            $track->genre = $t['genre'] ?? '';
            $track->duree = (int)$t['duree'];

            $playlist->addTrack($track);
        }

        return $playlist;
    }

    /**
     * Sauvegarde une piste en base et l'associe à la playlist spécifiée (Fonctionnalité 3)
     */
    public function saveTrack(AudioTrack $track, int $playlistId): AudioTrack {
        // 1. Insertion dans la table track
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

        // 2. Calcul du prochain numéro d'ordre de la piste dans la playlist
        $qNum = "SELECT COALESCE(MAX(no_piste_dans_liste), 0) + 1 AS next_num 
                 FROM playlist2track 
                 WHERE playlist_id = :pl_id";
        $stmtNum = $this->pdo->prepare($qNum);
        $stmtNum->execute(['pl_id' => $playlistId]);
        $nextNum = (int)$stmtNum->fetchColumn();

        // 3. Liaison dans playlist2track
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
