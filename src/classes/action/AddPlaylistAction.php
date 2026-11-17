<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class AddPlaylistAction extends Action {
    public function execute(): string {
        if ($this->http_method === 'GET') {
            return $this->renderForm();
        }

        // Requête POST : traitement et validation du formulaire
        $nom = filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $nom = trim($nom);

        if (empty($nom)) {
            $error = "<div class=\"alert alert-error\">Le nom de la playlist ne peut pas être vide.</div>";
            return $error . $this->renderForm();
        }

        try {
            // Création de l'objet Playlist
            $playlist = new Playlist($nom);

            // Persistance en base de données via le Repository
            $repo = DeefyRepository::getInstance();
            $playlist = $repo->saveEmptyPlaylist($playlist);

            // Définition de la playlist comme la playlist courante en session
            $_SESSION['current_playlist'] = $playlist;

            // Affichage de confirmation et du rendu de la playlist
            $renderer = new AudioListRenderer($playlist);
            $renderHtml = $renderer->render();

            $html = "<div class=\"card\">";
            $html .= "<div class=\"alert alert-success\">✅ La playlist \"<strong>" . htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong>\" a été créée avec succès et enregistrée en base de données !</div>";
            $html .= "<div class=\"action-toolbar\" style=\"margin-bottom: 1.5rem;\">";
            $html .= "<a class=\"btn btn-primary\" href=\"?action=add-track\">➕ Ajouter une piste à cette playlist</a> ";
            $html .= "<a class=\"btn\" href=\"?action=playlist-list\">📁 Voir toutes les playlists</a>";
            $html .= "</div>";
            $html .= $renderHtml;
            $html .= "</div>";

            return $html;
        } catch (\Exception $e) {
            $error = "<div class=\"alert alert-error\">Erreur lors de la création de la playlist : " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
            return $error . $this->renderForm();
        }
    }

    private function renderForm(): string {
        return <<<HTML
<div class="card form-card">
    <h2>Créer une nouvelle playlist vide</h2>
    <p>Saisissez le nom de la playlist. Une fois validée, elle sera enregistrée en base de données et deviendra votre playlist courante.</p>
    
    <form method="POST" action="?action=add-playlist" class="styled-form">
        <div class="form-group">
            <label for="nom">Nom de la playlist :</label>
            <input type="text" id="nom" name="nom" placeholder="Ex: Mes coups de cœur 2026, Jazz session..." required autofocus>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Créer la playlist</button>
            <a href="index.php" class="btn">Annuler</a>
        </div>
    </form>
</div>
HTML;
    }
}
