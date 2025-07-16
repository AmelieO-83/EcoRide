<?php

namespace App\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'insert:statistique',
    description: 'Insère une Statistique de test dans MongoDB',
)]
class InsertStatistiqueCommand extends Command
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        parent::__construct();
        $this->dm = $dm;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statistique = new \App\Document\Statistique();
        $statistique->setNom('Test ECF');
        $statistique->setValeur(42);
        $statistique->setDateCreation(new \DateTime());

        $this->dm->persist($statistique);
        $this->dm->flush();

        $output->writeln('<info>Statistique insérée dans MongoDB !</info>');
        return Command::SUCCESS;
    }
}
