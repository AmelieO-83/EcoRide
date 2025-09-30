<?php
// src/Entity/Utilisateur.php
namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\{
    PasswordAuthenticatedUserInterface,
    UserInterface
};
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ORM\UniqueConstraint(name: 'UNIQ_UTILISATEUR_EMAIL', fields: ['email'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    #[Groups(['utilisateur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank, Assert\Email]
    #[Groups(['utilisateur:read','utilisateur:write'])]
    private ?string $email = null;

    /** @var array<string> */
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['utilisateur:read'])]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\NotBlank(groups: ['utilisateur:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank, Assert\Length(min:2,max:64)]
    #[Groups(['utilisateur:read','utilisateur:write','participation:read', 'covoiturage:read','avis:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank, Assert\Length(min:2,max:64)]
    #[Groups(['utilisateur:read','utilisateur:write','participation:read', 'covoiturage:read','avis:read'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank, Assert\Regex('/^\d{10}$/')]
    #[Groups(['utilisateur:read','utilisateur:write'])]
    private ?string $telephone = null;

    #[ORM\Column(length: 64)]
    #[Assert\NotBlank]
    #[Groups(['utilisateur:read','utilisateur:write'])]
    private ?string $ville = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['utilisateur:read','utilisateur:write'])]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['utilisateur:read'])]
    private ?float $note = null;

    #[ORM\Column(type: 'integer', options: ['default' => 20])]
    #[Groups(['utilisateur:read'])]
    private int $credit = 20;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $apiToken;

    /** @var Collection<int, Voiture> */
    #[ORM\OneToMany(mappedBy: 'proprietaire', targetEntity: Voiture::class, orphanRemoval: true)]
    private Collection $voitures;

    /** @var Collection<int, Covoiturage> */
    #[ORM\OneToMany(mappedBy: 'chauffeur', targetEntity: Covoiturage::class)]
    private Collection $covoiturages;

    /** @var Collection<int, Participation> */
    #[ORM\OneToMany(mappedBy: 'passager', targetEntity: Participation::class, orphanRemoval: true)]
    private Collection $participations;

    /** @var Collection<int, Avis> */
    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Avis::class)]
    private Collection $avisDonnes;

    /** @var Collection<int, Avis> */
    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Avis::class)]
    private Collection $avisRecus;

    public function __construct()
    {
        $this->createdAt     = new \DateTimeImmutable();
        $this->apiToken      = bin2hex(random_bytes(16));
        $this->voitures      = new ArrayCollection();
        $this->covoiturages  = new ArrayCollection();
        $this->participations= new ArrayCollection();
        $this->avisDonnes    = new ArrayCollection();
        $this->avisRecus     = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $e): self { $this->email = $e; return $this; }

    public function getUserIdentifier(): string { return (string)$this->email; }
    public function getRoles(): array { return array_unique(array_merge($this->roles, ['ROLE_USER'])); }
    public function setRoles(array $r): self { $this->roles = $r; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $p): self { $this->password = $p; return $this; }

    public function eraseCredentials() {}

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(string $telephone): self { $this->telephone = $telephone; return $this; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(string $ville): self { $this->ville = $ville; return $this; }

    public function getDateNaissance(): ?\DateTimeInterface { return $this->dateNaissance; }
    public function setDateNaissance(\DateTimeInterface $dateNaissance): self { $this->dateNaissance = $dateNaissance; return $this; }

    public function getNote(): ?float { return $this->note; }
    public function setNote(?float $note): self { $this->note = $note; return $this; }

    public function getCredit(): int { return $this->credit; }
    public function setCredit(int $credit): self { $this->credit = $credit; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self {$this->createdAt = $createdAt; return $this;}
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function getApiToken(): string { return $this->apiToken; }
    public function setApiToken(string $apiToken): self { $this->apiToken = $apiToken; return $this; }

    // --- Collections ---
    public function getVoitures(): Collection { return $this->voitures; }
    public function addVoiture(Voiture $voitures): self {
        if(!$this->voitures->contains($voitures)){ $this->voitures->add($voitures); $voitures->setProprietaire($this);}
        return $this;
    }
    public function removeVoiture(Voiture $voitures): self {
        if($this->voitures->removeElement($voitures) && $voitures->getProprietaire()=== $this) {
            $voitures->setProprietaire(null);
        }
        return $this;
    }

    public function getCovoiturages(): Collection { return $this->covoiturages; }
    public function addCovoiturage(Covoiturage $covoiturages): self {
        if(!$this->covoiturages->contains($covoiturages)){ $this->covoiturages->add($covoiturages); $covoiturages->setChauffeur($this);}
        return $this;
    }
    public function removeCovoiturage(Covoiturage $covoiturages): self {
        if($this->covoiturages->removeElement($covoiturages) && $covoiturages->getChauffeur()=== $this){
            $covoiturages->setChauffeur(null);
        }
        return $this;
    }

    public function getParticipations(): Collection { return $this->participations; }
    public function addParticipation(Participation $participations): self {
        if(!$this->participations->contains($participations)){ $this->participations->add($participations); $participations->setPassager($this);}
        return $this;
    }
    public function removeParticipation(Participation $participations): self {
        if($this->participations->removeElement($participations) && $participations->getPassager()=== $this){
            $participations->setPassager(null);
        }
        return $this;
    }

    public function getAvisDonnes(): Collection { return $this->avisDonnes; }
    public function getAvisRecus(): Collection { return $this->avisRecus; }

}
