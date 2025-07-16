<?php
// src/Repository/MarqueRepository.php
namespace App\Repository;

use App\Entity\Marque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MarqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Marque::class);
    }

    /**
     * Cherche une marque par son libellé.
     */
    public function findOneByLibelle(string $libelle): ?Marque
    {
        return $this->findOneBy(['libelle' => $libelle]);
    }
}
