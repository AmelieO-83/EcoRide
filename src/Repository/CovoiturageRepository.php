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
     * @param string|null $energie     // "", "hybride" ou "electrique"
     * @param bool|null   $fumeur      // true ou false
     * @param bool|null   $animaux     // true ou false
     * @return Covoiturage[]
     */
        public function findByFilters(
            ?string $villeDepart,
            ?string $villeArrivee,
            ?\DateTimeImmutable $date,
            string $energie = '',
            bool $fumeur = false,
            bool $animaux = false
        ): array {
            $qb = $this->createQueryBuilder('c');

            if ('' !== $energie || $fumeur || $animaux) {
                // on ajoute les jointures nécessaires
                $qb->join('c.voiture', 'v');
            }

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
                ->setParameter('dt', $date->format('Y-m-d'));
            }
            if ('' !== $energie) {
                // on join seulement si on filtre l’énergie
                $qb->andWhere('v.energie = :energie')
                ->setParameter('energie', $energie);
            }
            if ($fumeur) {
                $qb->andWhere('v.fumeur = true');
            }
            if ($animaux) {
                $qb->andWhere('c.animaux = true');
            }
            return $qb
                ->orderBy('c.date', 'ASC')
                ->getQuery()
                ->getResult();
        }
}