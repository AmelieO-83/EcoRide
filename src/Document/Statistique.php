<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "statistiques")]
class Statistique
{
    #[MongoDB\Id]
    protected ?string $id = null;

    #[MongoDB\Field(type: 'string')]
    protected string $nom;

    #[MongoDB\Field(type: 'int')]
    protected int $valeur;

    // 👇 important : immutable car tu utilises DateTimeImmutable
    #[MongoDB\Field(type: 'date_immutable')]
    protected \DateTimeImmutable $dateCreation;

    public function getId(): ?string { return $this->id; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getValeur(): int { return $this->valeur; }
    public function setValeur(int $valeur): self { $this->valeur = $valeur; return $this; }

    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }
    public function setDateCreation(\DateTimeImmutable $date): self { $this->dateCreation = $date; return $this; }
}
