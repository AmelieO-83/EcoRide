<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904142511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage CHANGE chauffeur_id chauffeur_id INT NOT NULL');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E8985C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_28C79E8985C0B3BE ON covoiturage (chauffeur_id)');
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_ab55e24ffb88e14f TO IDX_AB55E24F71A51189');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E8985C0B3BE');
        $this->addSql('DROP INDEX IDX_28C79E8985C0B3BE ON covoiturage');
        $this->addSql('ALTER TABLE covoiturage CHANGE chauffeur_id chauffeur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE participation RENAME INDEX idx_ab55e24f71a51189 TO IDX_AB55E24FFB88E14F');
    }
}
