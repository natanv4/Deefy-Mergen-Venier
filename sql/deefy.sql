-- ==============================================================
-- Script de création et de remplissage de la base de données Deefy
-- BUT Informatique S3 - IUT Nancy-Charlemagne
-- ==============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS playlist2track;
DROP TABLE IF EXISTS track;
DROP TABLE IF EXISTS playlist;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Table des playlists
CREATE TABLE playlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table des pistes audio (morceaux d'albums ou podcasts)
CREATE TABLE track (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(256) NOT NULL,
    genre VARCHAR(64) DEFAULT NULL,
    duree INT NOT NULL DEFAULT 0,
    filename VARCHAR(256) NOT NULL,
    artiste_auteur VARCHAR(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table d'association playlist-piste avec ordre
CREATE TABLE playlist2track (
    playlist_id INT NOT NULL,
    track_id INT NOT NULL,
    no_piste_dans_liste INT NOT NULL DEFAULT 1,
    PRIMARY KEY (playlist_id, track_id),
    CONSTRAINT fk_pl2tr_playlist FOREIGN KEY (playlist_id) REFERENCES playlist(id) ON DELETE CASCADE,
    CONSTRAINT fk_pl2tr_track FOREIGN KEY (track_id) REFERENCES track(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table des utilisateurs (fournie pour compatibilité complète)
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(256) NOT NULL UNIQUE,
    passwd VARCHAR(256) NOT NULL,
    role INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- JEU D'ESSAI / REMPLISSAGE INITIAL
-- ==============================================================

-- Playlists de démonstration
INSERT INTO playlist (id, nom) VALUES
(1, 'Best of Rock'),
(2, 'Jazz & Chill'),
(3, 'Podcasts Tech');

-- Pistes audio de démonstration
INSERT INTO track (id, titre, genre, duree, filename, artiste_auteur) VALUES
(1, 'Bohemian Rhapsody', 'Rock', 354, 'audio/bohemian_rhapsody.mp3', 'Queen'),
(2, 'Stairway to Heaven', 'Rock', 482, 'audio/stairway_to_heaven.mp3', 'Led Zeppelin'),
(3, 'Autumn Leaves', 'Jazz', 285, 'audio/autumn_leaves.mp3', 'Miles Davis'),
(4, 'Take Five', 'Jazz', 324, 'audio/take_five.mp3', 'The Dave Brubeck Quartet'),
(5, 'L''IA du futur', 'Podcast', 1240, 'audio/podcast_ia.mp3', 'Tech News Daily');

-- Association des pistes aux playlists
INSERT INTO playlist2track (playlist_id, track_id, no_piste_dans_liste) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 3, 1),
(2, 4, 2),
(3, 5, 1);

-- Utilisateurs créés pour les tests (mots de passe hachés avec password_hash BCRYPT standard)
-- Tous les mots de passe en clair ci-dessous sont : user123
INSERT INTO user (id, email, passwd, role) VALUES
(1, 'user@deefy.fr', '$2y$10$wN9aW6vL6eJ2E7gPq7B8xOSoZzQjF90.63wHk5J5aL2b9M1c.bNKi', 1),
(2, 'admin@deefy.fr', '$2y$10$wN9aW6vL6eJ2E7gPq7B8xOSoZzQjF90.63wHk5J5aL2b9M1c.bNKi', 100),
(3, 'test@etu.univ-lorraine.fr', '$2y$10$wN9aW6vL6eJ2E7gPq7B8xOSoZzQjF90.63wHk5J5aL2b9M1c.bNKi', 1);
