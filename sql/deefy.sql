-- ==============================================================
-- Script de création et de remplissage de la base de données Deefy
-- BUT Informatique S3 - IUT Nancy-Charlemagne
-- ==============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS playlist2track;
DROP TABLE IF EXISTS track;
DROP TABLE IF EXISTS playlist;
SET FOREIGN_KEY_CHECKS = 1;

-- Table des playlists
CREATE TABLE playlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des pistes audio (morceaux d'albums ou podcasts)
CREATE TABLE track (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(256) NOT NULL,
    genre VARCHAR(64) DEFAULT NULL,
    duree INT NOT NULL DEFAULT 0,
    filename VARCHAR(256) NOT NULL,
    artiste_auteur VARCHAR(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table d'association entre playlists et pistes
CREATE TABLE playlist2track (
    playlist_id INT NOT NULL,
    track_id INT NOT NULL,
    no_piste_dans_liste INT NOT NULL DEFAULT 1,
    PRIMARY KEY (playlist_id, track_id),
    CONSTRAINT fk_pl2tr_playlist FOREIGN KEY (playlist_id) REFERENCES playlist(id) ON DELETE CASCADE,
    CONSTRAINT fk_pl2tr_track FOREIGN KEY (track_id) REFERENCES track(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données initiales de test
INSERT INTO playlist (id, nom) VALUES
(1, 'Best of Rock'),
(2, 'Jazz & Chill'),
(3, 'Podcasts Tech');

INSERT INTO track (id, titre, genre, duree, filename, artiste_auteur) VALUES
(1, 'Bohemian Rhapsody', 'Rock', 354, 'bohemian_rhapsody.mp3', 'Queen'),
(2, 'Stairway to Heaven', 'Rock', 482, 'stairway_to_heaven.mp3', 'Led Zeppelin'),
(3, 'Autumn Leaves', 'Jazz', 285, 'autumn_leaves.mp3', 'Miles Davis'),
(4, 'Take Five', 'Jazz', 324, 'take_five.mp3', 'The Dave Brubeck Quartet'),
(5, 'L''IA du futur', 'Technologie', 1240, 'podcast_ia.mp3', 'Tech News Daily');

INSERT INTO playlist2track (playlist_id, track_id, no_piste_dans_liste) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 3, 1),
(2, 4, 2),
(3, 5, 1);
