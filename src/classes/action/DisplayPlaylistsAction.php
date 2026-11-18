<?php

declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;

class DisplayPlaylistsAction extends Action {
    public function execute(): string {
        try {
            $repo = DeefyRepository::getInstance();
            $playlists = $repo->findAllPlaylists();

            $html = "<div class=\"card\">";
            $html .= "<div class=\"toolbar-header\" style=\"display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;\">";
            $html .= "<h2>Mes Playlists</h2>";
            $html .= "<a class=\"btn btn-primary\" href=\"?action=add-playlist\">➕ Créer une nouvelle playlist</a>";
            $html .= "</div>";

            if (empty($playlists)) {
                $html .= "<div class=\"empty-msg-box\">";
                $html .= "<p>Aucune playlist n'a encore été enregistrée dans la base de données.</p>";
                $html .= "<p>Vous pouvez importer les données initiales depuis le script <code>sql/deefy.sql</code> ou créer votre première playlist dès maintenant !</p>";
                $html .= "<a class=\"btn btn-primary\" href=\"?action=add-playlist\" style=\"margin-top: 1rem;\">Créer ma première playlist</a>";
                $html .= "</div>";
            } else {
                $html .= "<div class=\"playlists-grid\">";
                foreach ($playlists as $pl) {
                    $id = (int)$pl['id'];
                    $nom = htmlspecialchars($pl['nom'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $nbPistes = (int)($pl['nb_pistes'] ?? 0);
                    $labelPistes = $nbPistes > 1 ? "{$nbPistes} pistes" : ($nbPistes === 1 ? "1 piste" : "Aucune piste");

                    $html .= "<div class=\"playlist-card\">";
                    $html .= "<div class=\"playlist-card-content\">";
                    $html .= "<h3><a href=\"?action=display-playlist&id={$id}\">🎵 {$nom}</a></h3>";
                    $html .= "<p class=\"playlist-meta\">{$labelPistes}</p>";
                    $html .= "</div>";
                    $html .= "<div class=\"playlist-card-action\">";
                    $html .= "<a class=\"btn\" href=\"?action=display-playlist&id={$id}\">Ouvrir ▶</a>";
                    $html .= "</div>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            }

            $html .= "</div>";
            return $html;
        } catch (\Exception $e) {
            return "<div class=\"alert alert-error\">Erreur lors de la récupération des playlists : " . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</div>";
        }
    }
}
