<?php
// src/Command/StatsListCommand.php
namespace App\Command;

use App\Document\Statistique;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'stats:list',
    description: 'Affiche toutes les statistiques stockées en MongoDB',
)]
class StatsListCommand extends Command
{
    public function __construct(private DocumentManager $dm)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo  = $this->dm->getRepository(Statistique::class);
        $stats = $repo->findAll();

        if (empty($stats)) {
            $output->writeln('<comment>Aucune statistique en base.</comment>');
        } else {
            foreach ($stats as $stat) {
                $output->writeln(sprintf(
                    'Nom : %s | Valeur : %d | Date : %s',
                    $stat->getNom(),
                    $stat->getValeur(),
                    $stat->getDateCreation()->format('Y-m-d H:i:s')
                ));
            }
        }

        return Command::SUCCESS;
    }
}
