<?php

declare(strict_types=1);

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;

class AlbumTrackRenderer extends AudioTrackRenderer {
    public function __construct(AlbumTrack $track) {
        parent::__construct($track);
    }

    protected function renderCompact(): string {
        $titre = htmlspecialchars($this->track->titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $artiste = htmlspecialchars($this->track->artiste ?? 'Artiste inconnu', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $file = htmlspecialchars($this->track->filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "<div class=\"track-compact album-track\">";
        $html .= "<span class=\"track-title\"><strong>{$titre}</strong></span> - ";
        $html .= "<span class=\"track-artist\">{$artiste}</span>";
        if (!empty($this->track->filename)) {
            $html .= "<div class=\"audio-player\"><audio controls src=\"{$file}\"></audio></div>";
        }
        $html .= "</div>";
        return $html;
    }

    protected function renderLong(): string {
        $titre = htmlspecialchars($this->track->titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $artiste = htmlspecialchars($this->track->artiste ?? 'Inconnu', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $album = htmlspecialchars($this->track->album ?? 'Inconnu', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $genre = htmlspecialchars($this->track->genre ?? 'Non défini', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $annee = $this->track->annee !== null ? (string)$this->track->annee : 'Inconnue';
        $numero = $this->track->numero !== null ? (string)$this->track->numero : '-';
        $minutes = floor($this->track->duree / 60);
        $seconds = $this->track->duree % 60;
        $dureeFormatee = sprintf("%02d:%02d", $minutes, $seconds);
        $file = htmlspecialchars($this->track->filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = "<div class=\"track-long album-track\">";
        $html .= "<h3>{$titre}</h3>";
        $html .= "<ul>";
        $html .= "<li><strong>Artiste :</strong> {$artiste}</li>";
        $html .= "<li><strong>Album :</strong> {$album} (Piste n°{$numero})</li>";
        $html .= "<li><strong>Année :</strong> {$annee}</li>";
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
