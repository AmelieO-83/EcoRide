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
     * @param string|null             $villeDepart  Ville de départ
     * @param string|null             $villeArrivee Ville d'arrivée
     * @param \DateTimeImmutable|null $date         Date du trajet
     * @param string                  $energie      ""|"hybride"|"electrique"
     * @param bool                    $fumeur       true si fumeur autorisé
     * @param bool                    $animaux      true si animaux autorisés
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
        $qb = $this->createQueryBuilder('c');

        // Jointure véhicule si nécessaire pour les filtres liés à la voiture
        if ('' !== $energie || $fumeur || $animaux) {
            $qb->join('c.voiture', 'v');
        }

        // Filtre ville de départ
        if ($villeDepart) {
            $qb->andWhere('c.villeDepart = :dep')
               ->setParameter('dep', $villeDepart);
        }

        // Filtre ville d'arrivée
        if ($villeArrivee) {
            $qb->andWhere('c.villeArrivee = :arr')
               ->setParameter('arr', $villeArrivee);
        }

        // Filtre date (date-only)
        if ($date) {
            $qb->andWhere('c.date = :dt')
               ->setParameter('dt', $date);
        }

        // Filtre énergie (hybride / électrique)
        if ('' !== $energie) {
            $qb->andWhere('v.energie = :energie')
               ->setParameter('energie', $energie);
        }

        // Filtre fumeur autorisé
        if ($fumeur) {
            $qb->andWhere('v.fumeur = true');
        }

        // Filtre animaux autorisés
        if ($animaux) {
            $qb->andWhere('v.animaux = true');
        }

        return $qb
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
