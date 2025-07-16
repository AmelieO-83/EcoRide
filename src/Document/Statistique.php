<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class Statistique
{
    #[MongoDB\Id]
    protected $id;

    #[MongoDB\Field(type: 'string')]
    protected $nom;

    #[MongoDB\Field(type: 'int')]
    protected $valeur;

    #[MongoDB\Field(type: 'date')]
    protected $dateCreation;

    // getters & setters...
    public function getId(): ?string { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }
    public function getValeur(): ?int { return $this->valeur; }
    public function setValeur(int $valeur): self { $this->valeur = $valeur; return $this; }
    public function getDateCreation(): ?\DateTimeInterface { return $this->dateCreation; }
    public function setDateCreation(\DateTimeInterface $date): self { $this->dateCreation = $date; return $this; }
}
