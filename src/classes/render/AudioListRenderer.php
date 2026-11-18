<?php

declare(strict_types=1);

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;

class AudioListRenderer implements Renderer {
    protected $list;

    public function __construct(AudioList $list) {
        $this->list = $list;
    }

    public function render(int $selector = self::COMPACT): string {
        $nom = htmlspecialchars($this->list->nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $nbPistes = $this->list->nb_pistes;
        $dureeTotale = $this->list->duree;

        $minutes = floor($dureeTotale / 60);
        $seconds = $dureeTotale % 60;
        $dureeFormatee = sprintf("%02d:%02d", $minutes, $seconds);

        $html = "<div class=\"audiolist-details\">";
        $html .= "<h2>{$nom}</h2>";

        $html .= "<div class=\"track-list\">";
        if (empty($this->list->pistes)) {
            $html .= "<p class=\"empty-msg\">Aucune piste dans cette playlist pour le moment.</p>";
        } else {
            $html .= "<ol class=\"tracks-ol\">";
            foreach ($this->list->pistes as $piste) {
                $html .= "<li class=\"track-item\">";
                if ($piste instanceof AlbumTrack) {
                    $renderer = new AlbumTrackRenderer($piste);
                    $html .= $renderer->render(Renderer::COMPACT);
                } elseif ($piste instanceof PodcastTrack) {
                    $renderer = new PodcastRenderer($piste);
                    $html .= $renderer->render(Renderer::COMPACT);
                } else {
                    $titre = htmlspecialchars($piste->titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $html .= "<span>{$titre}</span>";
                }
                $html .= "</li>";
            }
            $html .= "</ol>";
        }
        $html .= "</div>";

        $html .= "<div class=\"audiolist-summary\">";
        $html .= "<p><strong>Nombre total de pistes :</strong> {$nbPistes}</p>";
        $html .= "<p><strong>Durée totale :</strong> {$dureeFormatee} ({$dureeTotale} secondes)</p>";
        $html .= "</div>";

        $html .= "</div>";

        return $html;
    }
}
