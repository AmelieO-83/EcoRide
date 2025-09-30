<?php
// src/Entity/Avis.php
namespace App\Entity;

use App\Entity\Utilisateur;
use App\Entity\Covoiturage;
use App\Repository\AvisRepository;
use App\Enum\AvisStatut;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\Table(name: 'avis')]
class Avis
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    #[Groups(['avis:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'avisDonnes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['avis:read'])]
    private Utilisateur $auteur;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'avisRecus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['avis:read'])]
    private Utilisateur $destinataire;

    #[ORM\Column(type: 'integer')]
    #[Groups(['avis:read','avis:write'])]
    private int $note;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['avis:read','avis:write'])]
    private string $commentaire;

    #[ORM\Column(enumType: AvisStatut::class)]
    #[Groups(['avis:read'])]
    private AvisStatut $statut = AvisStatut::EnAttente;

    #[ORM\ManyToOne(targetEntity: Covoiturage::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['avis:read'])]
    private ?Covoiturage $covoiturage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['avis:read'])]
    private ?\DateTimeImmutable $dateCreation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(Utilisateur $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getChauffeur(): Utilisateur
    {
        return $this->destinataire;
    }

    public function setChauffeur(Utilisateur $destinataire): static
    {
        $this->destinataire = $destinataire;
        return $this;
    }

    public function getNote(): int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getStatut(): AvisStatut
    {
        return $this->statut;
    }

    public function setStatut(AvisStatut $statut): static
    {
        $this->statut = $statut;
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

    public function __construct()
        {
            // à l’instanciation, on fixe la date de création
            $this->dateCreation = new \DateTimeImmutable();
            // si vous avez aussi un statut par défaut :
            $this->statut = AvisStatut::EnAttente;
        }

        public function getDateCreation(): \DateTimeImmutable
        {
            return $this->dateCreation;
        }
}
