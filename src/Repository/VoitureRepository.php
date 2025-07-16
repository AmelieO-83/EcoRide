<?php
// src/Repository/VoitureRepository.php
namespace App\Repository;

use App\Entity\Utilisateur;
use App\Entity\Voiture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoitureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, Voiture::class);
    }

    /**
     * Récupère toutes les voitures d’un utilisateur.
     *
     * @return Voiture[]
     */
    public function findByProprietaire(Utilisateur $user): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.proprietaire = :u')
            ->setParameter('u', $user)
            ->orderBy('v.modele', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
