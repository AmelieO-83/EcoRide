<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Utilisateur;
use App\Entity\Marque;
use App\Entity\Voiture;
use App\Entity\Covoiturage;
use App\Enum\EnergieType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $em): void
    {
        $faker = FakerFactory::create('fr_FR');

        // Unicité "maison"
        $usedPhones = [];
        $usedPlates = [];

        // Villes
        $citiesUser = [
            'Hyères','Toulon','La Seyne-sur-Mer','Six-Fours','Ollioules',
            'Marseille','Aix-en-Provence','Nice','Cannes','Antibes',
            'Fréjus','Saint-Raphaël','Draguignan','Bandol','Sanary',
        ];

        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException('Fixtures need an ORM EntityManager');
        }
        $conn = $em->getConnection();

        // ————————————— 1) UTILISATEURS —————————————
        $users = [];
        for ($i = 1; $i <= 16; $i++) {
            $u = (new Utilisateur())
                ->setEmail(sprintf('user%02d@ecoride.test', $i))
                ->setRoles(['ROLE_USER'])
                ->setPrenom($faker->firstName())
                ->setNom($faker->lastName());
            $u->setTelephone($this->uniqueFrMobile($usedPhones));
            $u->setVille($faker->randomElement($citiesUser));
            $u->setDateNaissance($faker->dateTimeBetween('-45 years','-18 years'));
            $u->setPassword($this->hasher->hashPassword($u, 'password'));
            $em->persist($u);
            $users[] = $u;
        }

        // ————————————— 2) MARQUES & VOITURES —————————————
        $brandNames = ['Renault','Peugeot','Citroën','Toyota','Volkswagen','Dacia'];
        $brands = [];
        foreach ($brandNames as $bn) {
            $m = (new Marque())->setLibelle($bn);
            $em->persist($m);
            $brands[] = $m;
        }

        $modelsPerBrand = [
            'Renault'     => ['Clio 4','Clio 5','Captur','Mégane'],
            'Peugeot'     => ['208','308','2008','3008'],
            'Citroën'     => ['C3','C3 Aircross','C4'],
            'Toyota'      => ['Yaris','Corolla','Auris'],
            'Volkswagen'  => ['Polo','Golf','T-Roc'],
            'Dacia'       => ['Sandero','Duster','Jogger'],
        ];

        $energies = EnergieType::cases();

        $cars = [];
        $carsTarget = $faker->numberBetween(18, 24);
        for ($i = 0; $i < $carsTarget; $i++) {
            $brand = $faker->randomElement($brands);
            $brandName = method_exists($brand, 'getLibelle') ? $brand->getLibelle() : null;
            $modele = $faker->randomElement($modelsPerBrand[$brandName] ?? ['Modèle']);

            $firstReg = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('2009-01-01', 'now')
            );

            $v = (new Voiture())
                ->setModele($modele)
                ->setMarque($brand);

            if (method_exists($v, 'setImmatriculation')) {
                $v->setImmatriculation($this->uniquePlate($usedPlates));
            }
            if (method_exists($v, 'setProprietaire')) {
                $v->setProprietaire($faker->randomElement($users));
            }
            if (method_exists($v, 'setEnergie')) {
                $v->setEnergie($faker->randomElement($energies)); // Enum case
            }
            if (method_exists($v, 'setDatePremiereImmatriculation')) {
                $v->setDatePremiereImmatriculation($firstReg);
            }
            if (method_exists($v, 'setCouleur')) {
                $v->setCouleur($faker->safeColorName());
            }

            $em->persist($v);
            $cars[] = $v;
        }

        // ————————————— 3) COVOITURAGES + PARTICIPATIONS par trajet —————————————
        $cities = [
            'Hyères','Toulon','La Seyne-sur-Mer','Six-Fours','Ollioules',
            'Marseille','Aix-en-Provence','Nice','Cannes','Antibes',
            'Fréjus','Saint-Raphaël','Draguignan','Bandol','Sanary',
            'Paris','Lyon','Montpellier'
        ];

        $ridesTarget = $faker->numberBetween(25, 40);
        $rides = [];

        for ($i = 0; $i < $ridesTarget; $i++) {
            $driver = $faker->randomElement($users);
            $car    = $faker->randomElement($cars);
            [$from, $to] = $this->pickTwoDistinct($cities, $faker);

            $date = (new \DateTimeImmutable())
                ->modify(($faker->numberBetween(-5,15)).' days')
                ->setTime($faker->randomElement([7,8,9,17,18]), $faker->randomElement([0,15,30,45]));

            // TIME_MUTABLE → \DateTime
            $departTime  = \DateTime::createFromFormat('H:i', $date->format('H:i')) ?: new \DateTime('08:00');
            $arriveeTime = (clone $departTime)->modify('+' . $faker->numberBetween(20,120) . ' minutes');

            $ride = (new Covoiturage())
                ->setVilleDepart($from)
                ->setVilleArrivee($to)
                ->setDate($date)
                ->setHeureDepart($departTime)
                ->setHeureArrivee($arriveeTime)
                ->setPrix($this->prixSelonDistance($from, $to))
                ->setPlacesDisponibles($faker->numberBetween(2,5))
                ->setChauffeur($driver)
                ->setVoiture($car);

            $em->persist($ride);
            $em->flush();                 // 🔒 ID covoiturage garanti

            $rideId = $ride->getId();
            if (!$rideId) {
                throw new \RuntimeException('Ride without id after flush.');
            }
            $rideManaged = $em->getRepository(Covoiturage::class)->find($rideId);
            if (!$rideManaged) {
                throw new \RuntimeException('Ride not found after flush (id='.$rideId.')');
            }

            // PARTICIPATIONS pour CE trajet, puis flush
            $maxPassengers = max(0, $rideManaged->getPlacesDisponibles() - 1);
            $nbPassengers  = $faker->numberBetween(0, min(3, $maxPassengers));

            if ($nbPassengers > 0) {
                $passengers = $this->pickDistinctPassengers($users, $rideManaged->getChauffeur(), $nbPassengers, $faker);

                $conn = $em->getConnection();
                foreach ($passengers as $p) {
                    $conn->insert('participation', [
                        'passager_id'     => $p->getId(),
                        'covoiturage_id'  => $rideManaged->getId(),   // 💡 FK jamais nulle
                        'confirme'        => $faker->boolean(80) ? 1 : 0,
                    ]);
                }
            }
        }

        // ————————————— 4) AVIS (optionnel) —————————————
        if (class_exists(Avis::class)) {
            foreach ($rides as $ride) {
                if ($ride->getDate() >= new \DateTimeImmutable('today')) {
                    continue;
                }
                $nb = $faker->numberBetween(0, 2);
                for ($i = 0; $i < $nb; $i++) {
                    $author = $faker->randomElement($users);
                    if ($author === $ride->getChauffeur()) { continue; }
                    $avis = (new Avis())
                        ->setChauffeur($ride->getChauffeur())
                        ->setAuteur($author)
                        ->setNote($faker->numberBetween(3,5))
                        ->setCommentaire($faker->sentence(10));
                    $em->persist($avis);
                }
            }
            $em->flush();
        }
    }

    /** @return array{0:string,1:string} */
    private function pickTwoDistinct(array $pool, \Faker\Generator $faker): array
    {
        $a = $faker->randomElement($pool);
        do { $b = $faker->randomElement($pool); } while ($b === $a);
        return [$a, $b];
    }

    /**
     * @param Utilisateur[] $users
     * @return Utilisateur[]
     */
    private function pickDistinctPassengers(array $users, Utilisateur $driver, int $count, \Faker\Generator $faker): array
    {
        $pool = array_values(array_filter($users, fn($u) => $u !== $driver));
        $picked = [];
        while (count($picked) < $count && !empty($pool)) {
            $p = $faker->randomElement($pool);
            if (!in_array($p, $picked, true)) {
                $picked[] = $p;
            }
        }
        return $picked;
    }

    private function prixSelonDistance(string $from, string $to): float
    {
        $shortPairs = ['Hyères-Toulon','Toulon-Hyères','Aix-en-Provence-Marseille','Marseille-Aix-en-Provence'];
        $key = $from.'-'.$to;
        if (in_array($key, $shortPairs, true)) {
            return 3.0 + mt_rand(0, 400) / 100.0; // 3.0–7.0
        }
        return 5.0 + mt_rand(0, 700) / 100.0;     // 5.0–12.0
    }

    // Génère un mobile FR "06########" unique
    private function uniqueFrMobile(array &$usedPhones): string
    {
        do {
            $phone = '06' . str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (isset($usedPhones[$phone]));
        $usedPhones[$phone] = true;
        return $phone;
    }

    // Génère une immatriculation FR "AA-123-AA" unique
    private function uniquePlate(array &$usedPlates): string
    {
        static $letters = ['A','B','C','D','E','F','G','H','J','K','L','M','N','P','R','S','T','U','V','W','X','Y','Z']; // sans I/O/Q
        do {
            $plate = $letters[array_rand($letters)]
                   . $letters[array_rand($letters)]
                   . '-' . str_pad((string)random_int(101, 899), 3, '0', STR_PAD_LEFT)
                   . '-' . $letters[array_rand($letters)]
                   . $letters[array_rand($letters)];
        } while (isset($usedPlates[$plate]));
        $usedPlates[$plate] = true;
        return $plate;
    }
}
