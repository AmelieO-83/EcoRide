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
            ?string $energie,
            ?bool $fumeur,
            ?bool $animaux
        ): array {
            $qb = $this->createQueryBuilder('c')
                // on join la voiture pour filtrer dessus
                ->join('c.voiture', 'v')
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
                $qb->andWhere('c.date = :dt')
                ->setParameter('dt', $date->format('Y-m-d'));
            }
            if ($energie) {
                $qb->andWhere('v.energie = :ene')
                ->setParameter('ene', $energie);
            }
            if (null !== $fumeur) {
                $qb->andWhere('v.fumeur = :fum')
                ->setParameter('fum', $fumeur);
            }
            if (null !== $animaux) {
                $qb->andWhere('v.animaux = :ani')
                ->setParameter('ani', $animaux);
            }

            return $qb
                ->orderBy('c.date', 'ASC')
                ->getQuery()
                ->getResult();
        }
}