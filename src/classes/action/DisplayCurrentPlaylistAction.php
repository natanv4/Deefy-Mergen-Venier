<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class DisplayCurrentPlaylistAction extends Action {
    public function execute(): string {
        // Cas Fonctionnalité 2 : chargement d'une playlist cliquée par son id
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                return "<div class=\"alert alert-error\">Identifiant de playlist invalide.</div>";
            }

            try {
                $playlist = DeefyRepository::getInstance()->findPlaylistById((int)$id);
                // La playlist chargée devient la playlist courante stockée en session
                $_SESSION['current_playlist'] = $playlist;
            } catch (\Exception $e) {
                return "<div class=\"alert alert-error\">Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
            }
        }

        // Cas Fonctionnalité 5 : affichage de la playlist courante en session
        if (!isset($_SESSION['current_playlist'])) {
            $html = "<div class=\"card\">";
            $html .= "<h2>Aucune playlist courante</h2>";
            $html .= "<p>Vous n'avez pas encore de playlist sélectionnée ou créée pour cette session.</p>";
            $html .= "<div class=\"home-actions\">";
            $html .= "<a class=\"btn btn-primary\" href=\"?action=add-playlist\">➕ Créer une nouvelle playlist</a> ";
            $html .= "<a class=\"btn\" href=\"?action=playlist-list\">📁 Choisir parmi mes playlists</a>";
            $html .= "</div>";
            $html .= "</div>";
            return $html;
        }

        $playlist = $_SESSION['current_playlist'];
        $renderer = new AudioListRenderer($playlist);
        $renderHtml = $renderer->render();

        $html = "<div class=\"card\">";
        $html .= "<div class=\"action-toolbar\" style=\"margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;\">";
        $html .= "<div>";
        $html .= "<a class=\"btn btn-primary\" href=\"?action=add-track\">➕ Ajouter une piste à cette playlist</a> ";
        $html .= "<a class=\"btn\" href=\"?action=playlist-list\">📁 Mes playlists</a>";
        $html .= "</div>";
        $html .= "<span class=\"badge-session\">Playlist courante en session</span>";
        $html .= "</div>";
        $html .= $renderHtml;
        $html .= "</div>";

        return $html;
    }
}
