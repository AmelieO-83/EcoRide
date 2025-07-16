<?php
// src/Repository/CovoiturageRepository.php
namespace App\Repository;

use App\Entity\Covoiturage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CovoiturageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Covoiturage::class);
    }

    /**
     * Recherche des covoiturages dispos selon filtres.
     *
     * @param string|null $villeDepart
     * @param string|null $villeArrivee
     * @param \DateTimeImmutable|null $date
     * @return Covoiturage[]
     */
    public function findByFilters(?string $villeDepart, ?string $villeArrivee, ?\DateTimeImmutable $date): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.placesDisponibles > 0');

        if ($villeDepart) {
            $qb->andWhere('c.villeDepart = :dep')
               ->setParameter('dep', $villeDepart);
        }
        if ($villeArrivee) {
            $qb->andWhere('c.villeArrivee = :arr')
               ->setParameter('arr', $villeArrivee);
        }
        if ($date) {
            $qb->andWhere('DATE(c.date) = :dt')
               ->setParameter('dt', $date->format('d-m-Y'));
        }

        return $qb
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
