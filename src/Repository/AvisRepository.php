<?php
// src/Repository/AvisRepository.php
namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, Avis::class);
    }

    /**
     * Récupère les avis reçus par un utilisateur, triés par date.
     * @return Avis[]
     */
    public function findRecusParUtilisateur(Utilisateur $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.destinataire = :usr')
            ->setParameter('usr', $user)
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
        return $this->findBy(['statut' => \App\Enum\AvisStatut::EnAttente], ['dateCreation'=>'ASC']);
    }
}
