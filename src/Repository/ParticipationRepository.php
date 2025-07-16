<?php
// src/Repository/ParticipationRepository.php
namespace App\Repository;

use App\Entity\Participation;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    /**
     * Récupère toutes les participations d’un utilisateur, triées par date descendante.
     * @return Participation[]
     */
    public function findByUser(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
