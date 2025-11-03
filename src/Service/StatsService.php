<?php
// src/Service/StatsService.php
namespace App\Service;

use App\Document\Statistique;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatsService
{
    public function __construct(private DocumentManager $dm) {}

    /**
     * Incrémente une statistique sur la journée courante (UTC).
     * Exemple: inc('rides_created'), inc('credits_earned', 5)
     */
    public function inc(string $nom, int $delta = 1): void
    {
        try {
            $now   = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            $start = $now->setTime(0, 0, 0);
            $end   = $start->modify('+1 day');

            $repo = $this->dm->getRepository(Statistique::class);
            $doc = $repo->createQueryBuilder()
                ->field('nom')->equals($nom)
                ->field('dateCreation')->gte($start)->lt($end)
                ->getQuery()->getSingleResult();

            if ($doc instanceof Statistique) {
                $doc->setValeur($doc->getValeur() + $delta);
            } else {
                $doc = (new Statistique())
                    ->setNom($nom)
                    ->setValeur($delta)
                    ->setDateCreation($now);
                $this->dm->persist($doc);
            }
            $this->dm->flush();
        } catch (\Throwable $e) {
            // On ne casse JAMAIS le flux métier pour une stat.
            // (tu peux logger si tu veux)
        }
    }
}
