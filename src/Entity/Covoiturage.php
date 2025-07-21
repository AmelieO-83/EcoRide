<?php
// src/Entity/Covoiturage.php
namespace App\Entity;

use App\Repository\CovoiturageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;
use App\Entity\Voiture;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
#[ORM\Table(name: 'covoiturage')]
class Covoiturage
{
    public const GROUP_PARTICIPATION_READ = 'participation:read';
    public const GROUP_COVOITURAGE_READ = 'covoiturage:read';
    public const GROUP_COVOITURAGE_WRITE = 'covoiturage:write';

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type:"integer")]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private ?string $villeDepart = null;

    #[ORM\Column(type:"string", length:255)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private ?string $villeArrivee = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    private ?\DateTimeInterface $heureDepart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'H:i'])]
    private ?\DateTimeInterface $heureArrivee = null;

    #[ORM\Column(type:"integer")]
    #[Groups([self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private int $placesDisponibles = 0;

    #[ORM\Column(type:"float")]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private float $prix = 0;

    #[ORM\Column(type:"boolean")]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private bool $ecologique = false;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "covoiturages")]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ])]
    #[SerializedName('chauffeur')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(targetEntity: Voiture::class, inversedBy: "covoiturages")]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups([self::GROUP_PARTICIPATION_READ, self::GROUP_COVOITURAGE_READ, self::GROUP_COVOITURAGE_WRITE])]
    private ?Voiture $voiture = null;

    #[ORM\OneToMany(mappedBy: "covoiturage", targetEntity: Participation::class, orphanRemoval: true)]
    private Collection $participations;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $annule = false;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'covoiturage')]
    private \Doctrine\Common\Collections\Collection $avis;

    public function __construct()
    {
        $this->participations = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getVilleDepart(): ?string { return $this->villeDepart; }
    public function setVilleDepart(string $villeDepart): self { $this->villeDepart = $villeDepart; return $this; }
    public function getVilleArrivee(): ?string { return $this->villeArrivee; }
    public function setVilleArrivee(string $villeArrivee): self { $this->villeArrivee = $villeArrivee; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }
    public function getHeureDepart(): ?\DateTimeInterface { return $this->heureDepart; }
    public function setHeureDepart(\DateTimeInterface $heureDepart): self { $this->heureDepart = $heureDepart; return $this; }
    public function getHeureArrivee(): ?\DateTimeInterface { return $this->heureArrivee; }
    public function setHeureArrivee(\DateTimeInterface $heureArrivee): self { $this->heureArrivee = $heureArrivee; return $this; }

    public function getPlacesDisponibles(): int { return $this->placesDisponibles; }
    public function setPlacesDisponibles(int $placesDisponibles): self { $this->placesDisponibles = $placesDisponibles; return $this; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): self { $this->prix = $prix; return $this; }

    public function isEcologique(): bool { return $this->ecologique; }
    public function setEcologique(bool $eco): self { $this->ecologique = $eco; return $this; }

    public function getChauffeur(): ?Utilisateur { return $this->utilisateur; }
    public function setChauffeur(?Utilisateur $utilisateur): self { $this->utilisateur = $utilisateur; return $this; }

    public function getVoiture(): ?Voiture { return $this->voiture; }
    public function setVoiture(?Voiture $voiture): self { $this->voiture = $voiture; return $this; }

    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participations): self
    {
        if (!$this->participations->contains($participations)) {
            $this->participations->add($participations);
            $participations->setCovoiturage($this);
        }
        return $this;
    }

    public function removeParticipation(Participation $participations): self
    {
        if ($this->participations->removeElement($participations) && $participations->getCovoiturage() === $this) {
            $participations->setCovoiturage(null);
        }
        return $this;
    }

    public function isAnnule(): bool {return $this->annule;}
    public function setAnnule(bool $annule): static {$this->annule = $annule; return $this;}

    /**
     * @return \Doctrine\Common\Collections\Collection<int, Avis>
     */
    public function getAvis(): \Doctrine\Common\Collections\Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setCovoiturage($this);
        }

        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getCovoiturage() === $this) {
                $avi->setCovoiturage(null);
            }
        }

        return $this;
    }
    /**
     * @return \Doctrine\Common\Collections\Collection<int, Utilisateur>
     */
    public function getPassagers(): Collection
    {
        $passagers = new ArrayCollection();
        foreach ($this->participations as $participation) {
            $passager = $participation->getPassager();
            if ($passager && !$passagers->contains($passager)) {
                $passagers->add($passager);
            }
        }

        return $passagers;
    }
}