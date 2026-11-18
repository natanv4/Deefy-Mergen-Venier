<?php

declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\NonEditablePropertyException;

class AudioList {
    protected $id = null;
    protected $nom;
    protected $nb_pistes = 0;
    protected $duree = 0; // durée totale en secondes
    /** @var AudioTrack[] */
    protected $pistes = [];

    /**
     * @param string $nom
     * @param AudioTrack[] $pistes
     */
    public function __construct(string $nom, array $pistes = []) {
        $this->nom = $nom;
        $this->pistes = [];
        $this->nb_pistes = 0;
        $this->duree = 0;

        foreach ($pistes as $piste) {
            if ($piste instanceof AudioTrack) {
                $this->pistes[] = $piste;
                $this->nb_pistes++;
                $this->duree += $piste->duree;
            }
        }
    }

    public function __get(string $name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new InvalidPropertyNameException("invalid property : $name");
    }

    public function __isset(string $name): bool {
        return property_exists($this, $name) && isset($this->$name);
    }

    public function __set(string $name, $value): void {
        if ($name === 'id') {
            $this->id = (int)$value;
            return;
        }
        throw new NonEditablePropertyException("property $name is not editable directly");
    }

    public function setId(int $id): void {
        $this->id = $id;
    }
}
