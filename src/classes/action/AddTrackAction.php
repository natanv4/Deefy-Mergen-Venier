<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class AddTrackAction extends Action {
    public function execute(): string {
        // Vérification de l'existence d'une playlist courante en session
        if (!isset($_SESSION['current_playlist'])) {
            $html = "<div class=\"card\">";
            $html .= "<div class=\"alert alert-error\">⚠️ Aucune playlist courante n'est sélectionnée.</div>";
            $html .= "<p>Vous devez d'abord créer une playlist ou en sélectionner une existante pour pouvoir y ajouter des pistes.</p>";
            $html .= "<div class=\"home-actions\">";
            $html .= "<a class=\"btn btn-primary\" href=\"?action=add-playlist\">➕ Créer une playlist</a> ";
            $html .= "<a class=\"btn\" href=\"?action=playlist-list\">📁 Choisir une playlist</a>";
            $html .= "</div>";
            $html .= "</div>";
            return $html;
        }

        $playlist = $_SESSION['current_playlist'];

        if ($this->http_method === 'GET') {
            return $this->renderForm($playlist->nom);
        }

        // Requête POST : validation et sanitization des données saisies
        $titre = filter_var($_POST['titre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $titre = trim($titre);

        $artisteAuteur = filter_var($_POST['artiste_auteur'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $artisteAuteur = trim($artisteAuteur);

        $typeTrack = filter_var($_POST['type_track'] ?? 'album', FILTER_SANITIZE_SPECIAL_CHARS);
        $genre = filter_var($_POST['genre'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $album = filter_var($_POST['album'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);

        $duree = filter_var($_POST['duree'] ?? 0, FILTER_VALIDATE_INT);
        if ($duree === false || $duree < 0) {
            $duree = 0;
        }

        $annee = filter_var($_POST['annee'] ?? null, FILTER_VALIDATE_INT);
        $numero = filter_var($_POST['numero'] ?? null, FILTER_VALIDATE_INT);

        // Validation des champs requis
        if (empty($titre)) {
            $error = "<div class=\"alert alert-error\">Le titre de la piste est obligatoire.</div>";
            return $error . $this->renderForm($playlist->nom);
        }

        // Gestion du fichier audio (upload ou nom de fichier fourni)
        $filename = "audio/default.mp3";
        $uploadDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES['audiofile']) && $_FILES['audiofile']['error'] === UPLOAD_ERR_OK) {
            $origName = $_FILES['audiofile']['name'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $allowedExts = ['mp3', 'ogg', 'wav', 'flac', 'aac', 'm4a'];

            if (in_array($ext, $allowedExts, true)) {
                $safeName = uniqid('track_') . '.' . $ext;
                $targetPath = $uploadDir . $safeName;
                if (move_uploaded_file($_FILES['audiofile']['tmp_name'], $targetPath)) {
                    $filename = "audio/" . $safeName;
                }
            }
        } elseif (!empty($_POST['filename'])) {
            $safeFilename = filter_var($_POST['filename'], FILTER_SANITIZE_SPECIAL_CHARS);
            $filename = trim($safeFilename);
        }

        try {
            // Création de la piste selon le type sélectionné
            if ($typeTrack === 'podcast') {
                $track = new PodcastTrack($titre, $filename);
                $track->auteur = !empty($artisteAuteur) ? $artisteAuteur : 'Auteur inconnu';
                $track->date = date('Y-m-d');
            } else {
                $track = new AlbumTrack($titre, $filename, !empty($album) ? $album : null, $numero !== false ? $numero : null);
                $track->artiste = !empty($artisteAuteur) ? $artisteAuteur : 'Artiste inconnu';
                if ($annee !== false && $annee !== null) {
                    $track->annee = $annee;
                }
            }

            $track->genre = !empty($genre) ? $genre : 'Divers';
            $track->duree = $duree;

            // Sauvegarde en base de données via le Repository
            $repo = DeefyRepository::getInstance();
            $pdo = $repo->getPdo();
            $plExists = false;
            if ($playlist->id !== null) {
                $chkStmt = $pdo->prepare("SELECT id FROM playlist WHERE id = :id");
                $chkStmt->execute(['id' => $playlist->id]);
                $plExists = (bool)$chkStmt->fetch();
            }
            if (!$plExists) {
                // Si la playlist en session n'est pas ou plus en base, on l'insère d'abord
                $playlist = $repo->saveEmptyPlaylist($playlist);
            }
            $track = $repo->saveTrack($track, $playlist->id);

            // Ajout de la piste à la playlist courante en session
            $playlist->addTrack($track);
            $_SESSION['current_playlist'] = $playlist;

            // Affichage de confirmation et du nouveau contenu de la playlist
            $renderer = new AudioListRenderer($playlist);
            $renderHtml = $renderer->render();

            $html = "<div class=\"card\">";
            $html .= "<div class=\"alert alert-success\">✅ La piste \"<strong>" . htmlspecialchars($titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong>\" a été ajoutée avec succès à la playlist !</div>";
            $html .= "<div class=\"action-toolbar\" style=\"margin-bottom: 1.5rem;\">";
            $html .= "<a class=\"btn btn-primary\" href=\"?action=add-track\">➕ Ajouter une autre piste</a> ";
            $html .= "<a class=\"btn\" href=\"?action=display-playlist\">👁️ Voir la playlist courante</a> ";
            $html .= "<a class=\"btn\" href=\"?action=playlist-list\">📁 Mes playlists</a>";
            $html .= "</div>";
            $html .= $renderHtml;
            $html .= "</div>";

            return $html;
        } catch (\Exception $e) {
            $error = "<div class=\"alert alert-error\">Erreur lors de l'ajout de la piste : " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
            return $error . $this->renderForm($playlist->nom);
        }
    }

    private function renderForm(string $playlistNom): string {
        $nomEscaped = htmlspecialchars($playlistNom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return <<<HTML
<div class="card form-card">
    <h2>Ajouter une piste à la playlist</h2>
    <p>Playlist de destination : <strong>{$nomEscaped}</strong></p>
    
    <form method="POST" action="?action=add-track" enctype="multipart/form-data" class="styled-form">
        <div class="form-group">
            <label for="titre">Titre de la piste * :</label>
            <input type="text" id="titre" name="titre" placeholder="Ex: Bohemian Rhapsody, Episode 1..." required autofocus>
        </div>

        <div class="form-group">
            <label for="type_track">Type de piste :</label>
            <select id="type_track" name="type_track">
                <option value="album">Piste musicale (Album)</option>
                <option value="podcast">Podcast</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="artiste_auteur">Artiste / Auteur :</label>
            <input type="text" id="artiste_auteur" name="artiste_auteur" placeholder="Ex: Queen, Tech News...">
        </div>

        <div class="form-group">
            <label for="album">Nom de l'album (optionnel) :</label>
            <input type="text" id="album" name="album" placeholder="Ex: A Night at the Opera">
        </div>

        <div class="form-row" style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label for="genre">Genre musical :</label>
                <input type="text" id="genre" name="genre" placeholder="Ex: Rock, Jazz, Tech...">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="duree">Durée (en secondes) :</label>
                <input type="number" id="duree" name="duree" placeholder="Ex: 354" min="0">
            </div>
        </div>

        <div class="form-row" style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label for="annee">Année de sortie :</label>
                <input type="number" id="annee" name="annee" placeholder="Ex: 1975" min="1900" max="2099">
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="numero">Numéro dans l'album :</label>
                <input type="number" id="numero" name="numero" placeholder="Ex: 11" min="1">
            </div>
        </div>

        <div class="form-group">
            <label for="audiofile">Téléverser un fichier audio (mp3, ogg, wav...) :</label>
            <input type="file" id="audiofile" name="audiofile" accept="audio/*">
        </div>

        <div class="form-group">
            <label for="filename">Ou chemin / nom de fichier audio existant :</label>
            <input type="text" id="filename" name="filename" placeholder="audio/ma_musique.mp3">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ajouter la piste</button>
            <a href="?action=display-playlist" class="btn">Retour à la playlist</a>
        </div>
    </form>
</div>
HTML;
    }
}
