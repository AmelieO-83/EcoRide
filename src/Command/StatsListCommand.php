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
    name: 'stats:list',
    description: 'Add a short description for your command',
)]
class StatsListCommand extends Command
{
    
    private DocumentManager $dm;
    public function __construct(DocumentManager $dm) {
        parent::__construct();
        $this->dm = $dm;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

protected function execute(InputInterface $input, OutputInterface $output): int
{
    $stats = $this->dm->getRepository(\App\Document\Statistique::class)->findAll();

    if (count($stats) === 0) {
        $output->writeln('<comment>Aucune statistique en base.</comment>');
    } else {
        foreach ($stats as $stat) {
            $output->writeln(
                sprintf('Nom : %s | Valeur : %d | Date : %s',
                    $stat->getNom(),
                    $stat->getValeur(),
                    $stat->getDateCreation()?->format('Y-m-d H:i')
                )
            );
        }
    }

    return Command::SUCCESS;
}

}
