<?php
// src/Command/StatsDeleteCommand.php
namespace App\Command;

use App\Document\Statistique;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ecoride:stats:delete',
    description: 'Supprime une statistique MongoDB par son nom',
)]
class StatsDeleteCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom de la statistique à supprimer');
    }

    public function __construct(private DocumentManager $dm)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nom  = $input->getArgument('nom');
        $repo = $this->dm->getRepository(Statistique::class);
        $stat = $repo->findOneBy(['nom' => $nom]);

        if (!$stat) {
            $output->writeln("<error>Aucune statistique trouvée pour « {$nom} ».</error>");
            return Command::FAILURE;
        }

        $this->dm->remove($stat);
        $this->dm->flush();

        $output->writeln("<info>Statistique « {$nom} » supprimée.</info>");
        return Command::SUCCESS;
    }
}