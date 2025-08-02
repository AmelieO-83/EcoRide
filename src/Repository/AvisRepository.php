<?php
// src/Repository/AvisRepository.php
namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Enum\AvisStatut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Récupère les avis reçus par un utilisateur, triés par date.
     * @return Avis[]
     */
    public function findRecusParUtilisateur(Utilisateur $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.destinataire = :usr')
            ->andWhere('a.statut = :statut')
            ->setParameter('usr', $user)
            ->setParameter('statut', AvisStatut::Valide->value)
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les avis en attente de validation.
     * @return Avis[]
     */
    public function findEnAttente(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', AvisStatut::EnAttente->value)
            ->orderBy('a.dateCreation', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findValides(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', AvisStatut::Valide->value)
            ->orderBy('a.dateCreation','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRejetes(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.statut = :statut')
            ->setParameter('statut', AvisStatut::Rejete->value)
            ->orderBy('a.dateCreation','DESC')
            ->getQuery()
            ->getResult();
    }
}
