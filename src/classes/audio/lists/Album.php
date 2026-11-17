<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\exception\InvalidPropertyNameException;

class Album extends AudioList {
    protected ?string $artiste = null;
    protected ?string $date_sortie = null;

    /**
     * @param string $nom
     * @param AudioTrack[] $pistes
     */
    public function __construct(string $nom, array $pistes) {
        parent::__construct($nom, $pistes);
    }

    public function setArtiste(string $artiste): void {
        $this->artiste = $artiste;
    }

    public function setDateSortie(string $date_sortie): void {
        $this->date_sortie = $date_sortie;
    }

    public function __set(string $name, mixed $value): void {
        if ($name === 'artiste') {
            $this->artiste = (string)$value;
            return;
        }
        if ($name === 'date_sortie') {
            $this->date_sortie = (string)$value;
            return;
        }
        parent::__set($name, $value);
    }
}
