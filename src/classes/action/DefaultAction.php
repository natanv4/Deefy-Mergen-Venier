<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

class DefaultAction extends Action {
    public function execute(): string {
        $html = "<div class=\"welcome-box\">";
        $html .= "<h2>Bienvenue sur Deefy !</h2>";
        $html .= "<p>L'application de streaming et de gestion de playlists audio de l'IUT Nancy-Charlemagne.</p>";
        $html .= "<div class=\"home-actions\">";
        $html .= "<a class=\"btn\" href=\"?action=playlist-list\">🎵 Voir mes playlists</a> ";
        $html .= "<a class=\"btn btn-primary\" href=\"?action=add-playlist\">➕ Créer une nouvelle playlist</a>";
        $html .= "</div>";
        $html .= "<div class=\"app-info\">";
        $html .= "<h3>Fonctionnalités disponibles :</h3>";
        $html .= "<ul>";
        $html .= "<li>Consulter les playlists enregistrées en base de données</li>";
        $html .= "<li>Afficher une playlist et écouter ses pistes audio</li>";
        $html .= "<li>Ajouter des pistes à la playlist courante</li>";
        $html .= "<li>Créer de nouvelles playlists</li>";
        $html .= "</ul>";
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }
}
