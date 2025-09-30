<?php
// src/Command/InsertStatistiqueCommand.php
namespace App\Command;

use App\Document\Statistique;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'stats:insert',
    description: 'Insère une nouvelle statistique dans MongoDB',
)]
class InsertStatistiqueCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom de la statistique')
            ->addArgument('valeur', InputArgument::REQUIRED, 'Valeur numérique');
    }

    public function __construct(private DocumentManager $dm)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nom    = $input->getArgument('nom');
        $valeur = (int)$input->getArgument('valeur');

        $stat = new Statistique();
        $stat->setNom($nom)
             ->setValeur($valeur)
             ->setDateCreation(new \DateTimeImmutable());

        $this->dm->persist($stat);
        $this->dm->flush();

        $output->writeln("<info>Statistique « {$nom} : {$valeur} » insérée.</info>");
        return Command::SUCCESS;
    }
}
