================================================================================

&#x20;                    	PROJET DEEFY - RENDU

&#x20;                      BUT Informatique S3 - 2026

&#x20;                       IUT Nancy-Charlemagne

================================================================================



Binôme :

\- Lola MERGEN

\- Natan VENIER



Dépôt Git : https://github.com/natanv4/Deefy-Mergen-Venier

Déploiement Webetu : https://webetu.iutnc.univ-lorraine.fr/~e94637u/Deefy-Mergen-Venier/index.php



================================================================================

1\. TABLEAU DE BORD DES FONCTIONNALITÉS RÉALISÉES

================================================================================



1\. Mes playlistes sotckées : RÉALISÉE



2\. chaque élément de la liste est cliquable et permet d’afficher une playlist qui devient la

playlist courante stockée en session : RÉALISÉE



3\. Ajouter une piste : ajoute une nouvelle piste à la playlist courante ; un formulaire est affiché

pour saisir les données de la piste. Cette fonctionnalité est accessible dans l’affichage d’une

playlist : RÉALISÉE



4\. créer une playlist vide : un formulaire permettant de saisir le nom d’une nouvelle playlist est

affiché. A la validation, la playlist est créée et stockée en BD ; elle devient la playlist

courante : RÉALISÉE



5\. Afficher la playlist courante : affiche la playlist stockée en session : RÉALISÉE





================================================================================

2\. SCRIPT SQL

================================================================================



CREATE TABLE playlist (

&#x20;   id INT AUTO\_INCREMENT PRIMARY KEY,

&#x20;   nom VARCHAR(256) NOT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4\_unicode\_ci;



CREATE TABLE track (

&#x20;   id INT AUTO\_INCREMENT PRIMARY KEY,

&#x20;   titre VARCHAR(256) NOT NULL,

&#x20;   genre VARCHAR(64) DEFAULT NULL,

&#x20;   duree INT NOT NULL DEFAULT 0,

&#x20;   filename VARCHAR(256) NOT NULL,

&#x20;   artiste\_auteur VARCHAR(256) DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4\_unicode\_ci;



CREATE TABLE playlist2track (

&#x20;   playlist\_id INT NOT NULL,

&#x20;   track\_id INT NOT NULL,

&#x20;   no\_piste\_dans\_liste INT NOT NULL DEFAULT 1,

&#x20;   PRIMARY KEY (playlist\_id, track\_id),

&#x20;   CONSTRAINT fk\_pl2tr\_playlist FOREIGN KEY (playlist\_id) REFERENCES playlist(id) ON DELETE CASCADE,

&#x20;   CONSTRAINT fk\_pl2tr\_track FOREIGN KEY (track\_id) REFERENCES track(id) ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4\_unicode\_ci;



CREATE TABLE user (

&#x20;   id INT AUTO\_INCREMENT PRIMARY KEY,

&#x20;   email VARCHAR(256) NOT NULL UNIQUE,

&#x20;   passwd VARCHAR(256) NOT NULL,

&#x20;   role INT NOT NULL DEFAULT 1

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4\_unicode\_ci;

