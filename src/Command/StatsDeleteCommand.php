<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ODM\MongoDB\DocumentManager;

#[AsCommand(
    name: 'stats:delete',
    description: 'Add a short description for your command',
)]
class StatsDeleteCommand extends Command
{
    private DocumentManager $dm;
    public function __construct(DocumentManager $dm) {
        parent::__construct();
        $this->dm = $dm;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Supprime une statistique MongoDB par son nom')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom de la statistique à supprimer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nom = $input->getArgument('nom');
        $repo = $this->dm->getRepository(\App\Document\Statistique::class);
        $stat = $repo->findOneBy(['nom' => $nom]);

        if (!$stat) {
            $output->writeln('<error>Aucune statistique trouvée avec ce nom.</error>');
            return Command::FAILURE;
        }

        $this->dm->remove($stat);
        $this->dm->flush();

        $output->writeln('<info>Statistique supprimée avec succès !</info>');
        return Command::SUCCESS;
    }

}
