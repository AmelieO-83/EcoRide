<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716085505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, covoiturage_id INT NOT NULL, statut TINYINT(1) NOT NULL, INDEX IDX_AB55E24FFB88E14F (utilisateur_id), INDEX IDX_AB55E24F62671590 (covoiturage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FFB88E14F');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F62671590');
        $this->addSql('DROP TABLE participation');
    }
}
