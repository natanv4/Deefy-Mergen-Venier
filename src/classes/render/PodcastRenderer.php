<?php

declare(strict_types=1);

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\PodcastTrack;

class PodcastRenderer extends AudioTrackRenderer {
    public function __construct(PodcastTrack $track) {
        parent::__construct($track);
    }

    protected function renderCompact(): string {
        $titre = htmlspecialchars($this->track->titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $auteur = htmlspecialchars($this->track->auteur ?? 'Auteur inconnu', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $file = htmlspecialchars($this->track->filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "<div class=\"track-compact podcast-track\">";
        $html .= "<span class=\"track-title\"><strong>{$titre}</strong></span> (Podcast) - ";
        $html .= "<span class=\"track-author\">{$auteur}</span>";
        if (!empty($this->track->filename)) {
            $html .= "<div class=\"audio-player\"><audio controls src=\"{$file}\"></audio></div>";
        }
        $html .= "</div>";
        return $html;
    }

    protected function renderLong(): string {
        $titre = htmlspecialchars($this->track->titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $auteur = htmlspecialchars($this->track->auteur ?? 'Inconnu', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $date = htmlspecialchars($this->track->date ?? 'Inconnue', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $genre = htmlspecialchars($this->track->genre ?? 'Non défini', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $minutes = floor($this->track->duree / 60);
        $seconds = $this->track->duree % 60;
        $dureeFormatee = sprintf("%02d:%02d", $minutes, $seconds);
        $file = htmlspecialchars($this->track->filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "<div class=\"track-long podcast-track\">";
        $html .= "<h3>{$titre} (Podcast)</h3>";
        $html .= "<ul>";
        $html .= "<li><strong>Auteur :</strong> {$auteur}</li>";
        $html .= "<li><strong>Date d'enregistrement :</strong> {$date}</li>";
        $html .= "<li><strong>Genre :</strong> {$genre}</li>";
        $html .= "<li><strong>Durée :</strong> {$dureeFormatee} ({$this->track->duree}s)</li>";
        $html .= "</ul>";
        if (!empty($this->track->filename)) {
            $html .= "<div class=\"audio-player\"><audio controls src=\"{$file}\"></audio></div>";
        }
        $html .= "</div>";
        return $html;
    }
}
