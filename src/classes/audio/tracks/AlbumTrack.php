<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;

class AlbumTrack extends AudioTrack {
    protected $artiste = null;
    protected $album = null;
    protected $annee = null;
    protected $numero = null;

    public function __construct(string $titre, string $filename, ?string $album = null, ?int $numero = null) {
        parent::__construct($titre, $filename);
        $this->album = $album;
        $this->numero = $numero;
    }

    public function __set(string $name, $value): void {
        if (!property_exists($this, $name)) {
            throw new InvalidPropertyNameException("invalid property : $name");
        }

        if (in_array($name, ['album', 'numero', 'artiste', 'annee'], true)) {
            if (($name === 'numero' || $name === 'annee') && $value !== null && $value < 0) {
                throw new InvalidPropertyValueException("invalid value for $name : $value");
            }
            $this->$name = $value;
            return;
        }

        parent::__set($name, $value);
    }
}
