<?php
// src/Entity/Participation.php
namespace App\Entity;

use App\Entity\Utilisateur;
use App\Entity\Covoiturage;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(name: 'participation')]
class Participation
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['participation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $passager = null;

    #[ORM\ManyToOne(targetEntity: Covoiturage::class, inversedBy: 'participations')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['participation:read'])]
    private ?Covoiturage $covoiturage = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['participation:read'])]
    private bool $confirme = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassager(): ?Utilisateur
    {
        return $this->passager;
    }

    public function setPassager(?Utilisateur $utilisateur): static
    {
        $this->passager = $utilisateur;
        return $this;
    }

    public function getCovoiturage(): ?Covoiturage
    {
        return $this->covoiturage;
    }

    public function setCovoiturage(?Covoiturage $covoiturage): static
    {
        $this->covoiturage = $covoiturage;
        return $this;
    }

    public function isConfirme(): bool
    {
        return $this->confirme;
    }

    public function setConfirme(bool $confirme): static
    {
        $this->confirme = $confirme;
        return $this;
    }
}
