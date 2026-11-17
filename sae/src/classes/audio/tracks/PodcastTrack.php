<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;

class PodcastTrack extends AudioTrack {
    protected ?string $auteur = null;
    protected ?string $date = null;

    public function __construct(string $titre, string $filename) {
        parent::__construct($titre, $filename);
    }

    public function __set(string $name, mixed $value): void {
        if (!property_exists($this, $name)) {
            throw new InvalidPropertyNameException("invalid property : $name");
        }

        if (in_array($name, ['auteur', 'date'], true)) {
            $this->$name = $value;
            return;
        }

        parent::__set($name, $value);
    }
}
