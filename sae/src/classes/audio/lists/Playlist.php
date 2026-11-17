<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;

class Playlist extends AudioList {
    /**
     * @param string $nom
     * @param AudioTrack[] $pistes
     */
    public function __construct(string $nom, array $pistes = []) {
        parent::__construct($nom, $pistes);
    }

    public function addTrack(AudioTrack $piste): void {
        $this->pistes[] = $piste;
        $this->nb_pistes++;
        $this->duree += $piste->duree;
    }

    public function removeTrack(int $index): void {
        if (isset($this->pistes[$index])) {
            $this->duree -= $this->pistes[$index]->duree;
            if ($this->duree < 0) {
                $this->duree = 0;
            }
            array_splice($this->pistes, $index, 1);
            $this->nb_pistes = count($this->pistes);
        }
    }

    /**
     * @param AudioTrack[] $pistes
     */
    public function addTracks(array $pistes): void {
        foreach ($pistes as $piste) {
            if ($piste instanceof AudioTrack) {
                // Évite l'ajout de doublons stricts (même instance ou même id si défini)
                $isDuplicate = false;
                foreach ($this->pistes as $existing) {
                    if ($existing === $piste) {
                        $isDuplicate = true;
                        break;
                    }
                    if ($existing->id !== null && $piste->id !== null && $existing->id === $piste->id) {
                        $isDuplicate = true;
                        break;
                    }
                }
                if (!$isDuplicate) {
                    $this->addTrack($piste);
                }
            }
        }
    }
}
