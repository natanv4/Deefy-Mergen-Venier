<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;
use iutnc\deefy\exception\NonEditablePropertyException;

abstract class AudioTrack {
    protected $id = null;
    protected $titre;
    protected $genre = null;
    protected $duree = 0; // durée en secondes
    protected $filename;

    public function __construct(string $titre, string $filename) {
        $this->titre = $titre;
        $this->filename = $filename;
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new InvalidPropertyNameException("invalid property : $name");
    }

    public function __set(string $name, $value): void {
        if (!property_exists($this, $name)) {
            throw new InvalidPropertyNameException("invalid property : $name");
        }

        if ($name === 'titre' || $name === 'filename') {
            throw new NonEditablePropertyException("property $name is not editable");
        }

        if ($name === 'duree') {
            if ($value < 0) {
                throw new InvalidPropertyValueException("invalid duration value : $value");
            }
            $this->duree = (int)$value;
            return;
        }

        if ($name === 'genre') {
            $this->genre = (string)$value;
            return;
        }

        if ($name === 'id') {
            $this->id = (int)$value;
            return;
        }

        $this->$name = $value;
    }

    public function __isset(string $name): bool {
        return property_exists($this, $name) && isset($this->$name);
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function __toString(): string {
        return (string)json_encode(get_object_vars($this));
    }
}
