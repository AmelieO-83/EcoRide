<?php
// src/Entity/Voiture.php
namespace App\Entity;

use App\Repository\VoitureRepository;
use App\Entity\Utilisateur;
use App\Entity\Marque;
use App\Entity\Covoiturage;
use App\Enum\EnergieType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VoitureRepository::class)]
#[ORM\Table(name: 'voiture')]
class Voiture
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['voiture:read','voiture:write', 'covoiturage:read','participation:read'])]
    private ?string $modele = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Groups(['voiture:read','voiture:write'])]
    private ?string $immatriculation = null;

    #[ORM\Column(enumType: EnergieType::class)]
    #[Groups(['voiture:read','voiture:write'])]
    private ?EnergieType $energie = null;

    #[ORM\Column(type: 'string', length: 64)]
    #[Groups(['voiture:read','voiture:write'])]
    private ?string $couleur = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['voiture:read','voiture:write'])]
    private ?\DateTimeInterface $datePremiereImmatriculation = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['voiture:read','voiture:write'])]
    private bool $fumeur = false;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['voiture:read','voiture:write'])]
    private bool $animaux = false;

    #[Groups(['covoiturage:read','participation:read'])]
    #[ORM\ManyToOne(targetEntity: Marque::class, inversedBy: 'voitures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Marque $marque = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'voitures')]
    #[ORM\JoinColumn(name:'proprietaire_id', referencedColumnName:'id', nullable: false)]
    private ?Utilisateur $proprietaire = null;

    #[ORM\OneToMany(mappedBy: 'voiture', targetEntity: Covoiturage::class, orphanRemoval: true)]
    private Collection $covoiturages;

    public function __construct()
    {
        $this->covoiturages = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getModele(): ?string { return $this->modele; }
    public function setModele(string $m): static { $this->modele = $m; return $this; }

    public function getImmatriculation(): ?string { return $this->immatriculation; }
    public function setImmatriculation(string $i): static { $this->immatriculation = $i; return $this; }

    public function getEnergie(): ?EnergieType { return $this->energie; }
    public function setEnergie(EnergieType $e): static { $this->energie = $e; return $this; }

    public function getCouleur(): ?string { return $this->couleur; }
    public function setCouleur(string $c): static { $this->couleur = $c; return $this; }

    public function getDatePremiereImmatriculation(): ?\DateTimeInterface { return $this->datePremiereImmatriculation; }
    public function setDatePremiereImmatriculation(\DateTimeInterface $d): static { $this->datePremiereImmatriculation = $d; return $this; }

    public function isFumeur(): bool { return $this->fumeur; }
    public function setFumeur(bool $f): static { $this->fumeur = $f; return $this; }

    public function isAnimaux(): bool { return $this->animaux; }
    public function setAnimaux(bool $a): static { $this->animaux = $a; return $this; }

    public function getMarque(): ?Marque { return $this->marque; }
    public function setMarque(?Marque $m): static { $this->marque = $m; return $this; }

    public function getProprietaire(): ?Utilisateur { return $this->proprietaire; }
    public function setProprietaire(?Utilisateur $proprietaire): static { $this->proprietaire = $proprietaire; return $this; }

    public function getCovoiturages(): Collection{return $this->covoiturages;}
    public function addCovoiturage(Covoiturage $covoiturage): self
    {
        if (!$this->covoiturages->contains($covoiturage)) {
            $this->covoiturages->add($covoiturage);
            $covoiturage->setVoiture($this);
        }
        return $this;
    }

    public function removeCovoiturage(Covoiturage $covoiturage): self
    {
        if ($this->covoiturages->removeElement($covoiturage)) {
            if ($covoiturage->getVoiture() === $this) {
                $covoiturage->setVoiture(null);
            }
        }
        return $this;
    }
}
