<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251003094242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, destinataire_id INT NOT NULL, covoiturage_id INT NOT NULL, note INT NOT NULL, commentaire LONGTEXT NOT NULL, statut VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8F91ABF060BB6FE6 (auteur_id), INDEX IDX_8F91ABF0A4F84F6E (destinataire_id), INDEX IDX_8F91ABF062671590 (covoiturage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE covoiturage (id INT AUTO_INCREMENT NOT NULL, chauffeur_id INT NOT NULL, voiture_id INT DEFAULT NULL, ville_depart VARCHAR(255) NOT NULL, ville_arrivee VARCHAR(255) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', heure_depart TIME NOT NULL, heure_arrivee TIME NOT NULL, places_disponibles INT NOT NULL, prix DOUBLE PRECISION NOT NULL, ecologique TINYINT(1) NOT NULL, annule TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_28C79E8985C0B3BE (chauffeur_id), INDEX IDX_28C79E89181A8BA (voiture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE marque (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, passager_id INT NOT NULL, covoiturage_id INT NOT NULL, confirme TINYINT(1) NOT NULL, INDEX IDX_AB55E24F71A51189 (passager_id), INDEX IDX_AB55E24F62671590 (covoiturage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(64) NOT NULL, prenom VARCHAR(64) NOT NULL, telephone VARCHAR(10) NOT NULL, ville VARCHAR(64) NOT NULL, date_naissance DATE NOT NULL, note DOUBLE PRECISION DEFAULT NULL, credit INT DEFAULT 20 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', api_token VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B37BA2F5EB (api_token), UNIQUE INDEX UNIQ_UTILISATEUR_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE voiture (id INT AUTO_INCREMENT NOT NULL, marque_id INT NOT NULL, proprietaire_id INT NOT NULL, modele VARCHAR(255) NOT NULL, immatriculation VARCHAR(10) NOT NULL, energie VARCHAR(255) NOT NULL, couleur VARCHAR(64) NOT NULL, date_premiere_immatriculation DATE NOT NULL, fumeur TINYINT(1) NOT NULL, animaux TINYINT(1) NOT NULL, INDEX IDX_E9E2810F4827B9B2 (marque_id), INDEX IDX_E9E2810F76C50E4A (proprietaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF060BB6FE6 FOREIGN KEY (auteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF062671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id)');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E8985C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE covoiturage ADD CONSTRAINT FK_28C79E89181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F71A51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F62671590 FOREIGN KEY (covoiturage_id) REFERENCES covoiturage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F4827B9B2 FOREIGN KEY (marque_id) REFERENCES marque (id)');
        $this->addSql('ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF060BB6FE6');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A4F84F6E');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF062671590');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E8985C0B3BE');
        $this->addSql('ALTER TABLE covoiturage DROP FOREIGN KEY FK_28C79E89181A8BA');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F71A51189');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F62671590');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F4827B9B2');
        $this->addSql('ALTER TABLE voiture DROP FOREIGN KEY FK_E9E2810F76C50E4A');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE covoiturage');
        $this->addSql('DROP TABLE marque');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE voiture');
    }
}
