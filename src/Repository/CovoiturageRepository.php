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
     * Recherche des covoiturages disponibles selon les filtres.
     *
     * @param string|null             $villeDepart   Ville de départ
     * @param string|null             $villeArrivee  Ville d'arrivée
     * @param \DateTimeImmutable|null $date          Date du trajet
     * @param string                  $energie       "" | "hybride" | "electrique" | "essence"...
     * @param bool                    $fumeur        true si fumeur autorisé
     * @param bool                    $animaux       true si animaux autorisés
     *
     * @return Covoiturage[]
     */
    public function findByFilters(
        ?string $villeDepart,
        ?string $villeArrivee,
        ?\DateTimeImmutable $date,
        string $energie  = '',
        bool   $fumeur   = false,
        bool   $animaux  = false
    ): array {
        // Eager-load de la voiture pour (1) le badge éco dans la liste et (2) éviter N+1
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.voiture', 'v')->addSelect('v');

        $qb->andWhere('c.placesDisponibles > 0');

        if ($villeDepart) {
            $qb->andWhere('c.villeDepart = :dep')
               ->setParameter('dep', $villeDepart);
        }

        if ($villeArrivee) {
            $qb->andWhere('c.villeArrivee = :arr')
               ->setParameter('arr', $villeArrivee);
        }

        if ($date) {
            $qb->andWhere('c.date = :dt')
               ->setParameter('dt', $date);
        }

        if ('' !== $energie) {
            $qb->andWhere('LOWER(v.energie) = :energie')
               ->setParameter('energie', strtolower($energie));
        }
        if ($fumeur) {
            $qb->andWhere('v.fumeur = 1');
        }
        if ($animaux) {
            $qb->andWhere('v.animaux = 1');
        }

        return $qb
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
