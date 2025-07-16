<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250716145812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP titre, CHANGE note note INT NOT NULL, CHANGE created_at date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE covoiturage ADD annule TINYINT(1) DEFAULT 0 NOT NULL, DROP created_at, DROP updated_at, CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL, CHANGE voiture_id voiture_id INT DEFAULT NULL, CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE marque CHANGE libelle libelle VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA62671590');
        $this->addSql('DROP INDEX IDX_BF5476CAFB88E14F ON notification');
        $this->addSql('DROP INDEX IDX_BF5476CA62671590 ON notification');
        $this->addSql('ALTER TABLE notification DROP utilisateur_id, DROP covoiturage_id, DROP date_envoi, CHANGE titre titre VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE participation CHANGE covoiturage_id covoiturage_id INT DEFAULT NULL, CHANGE statut confirme TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD api_token VARCHAR(64) NOT NULL, CHANGE prenom prenom VARCHAR(64) NOT NULL, CHANGE credit credit INT DEFAULT 20 NOT NULL');
        $this->addSql('DROP INDEX uniq_identifier_email ON utilisateur');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_UTILISATEUR_EMAIL ON utilisateur (email)');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810FFB88E14F');
        $this->addSql('DROP INDEX IDX_E9E2810FFB88E14F ON voiture');
        $this->addSql('ALTER TABLE voiture DROP created_at, DROP updated_at, CHANGE modele modele VARCHAR(255) NOT NULL, CHANGE immatriculation immatriculation VARCHAR(10) NOT NULL, CHANGE couleur couleur VARCHAR(64) NOT NULL, CHANGE utilisateur_id proprietaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E9E2810F76C50E4A ON voiture (proprietaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis ADD titre VARCHAR(64) NOT NULL, CHANGE note note DOUBLE PRECISION NOT NULL, CHANGE date_creation created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE covoiturage ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP annule, CHANGE utilisateur_id utilisateur_id INT NOT NULL, CHANGE voiture_id voiture_id INT NOT NULL, CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE marque CHANGE libelle libelle VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD utilisateur_id INT NOT NULL, ADD covoiturage_id INT NOT NULL, ADD date_envoi DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE titre titre VARCHAR(64) NOT NULL');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CAFB88E14F ON notification (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA62671590 ON notification (covoiturage_id)');
        $this->addSql('ALTER TABLE participation CHANGE covoiturage_id covoiturage_id INT NOT NULL, CHANGE confirme statut TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE utilisateur DROP api_token, CHANGE prenom prenom VARCHAR(32) NOT NULL, CHANGE credit credit INT DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_utilisateur_email ON utilisateur');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON utilisateur (email)');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F76C50E4A');
        $this->addSql('DROP INDEX IDX_E9E2810F76C50E4A ON voiture');
        $this->addSql('ALTER TABLE voiture ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modele modele VARCHAR(64) NOT NULL, CHANGE immatriculation immatriculation VARCHAR(32) NOT NULL, CHANGE couleur couleur VARCHAR(32) NOT NULL, CHANGE proprietaire_id utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E9E2810FFB88E14F ON voiture (utilisateur_id)');
    }
}
