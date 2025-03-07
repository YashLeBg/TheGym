<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250307030909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_de_paie ADD total_seances INT NOT NULL');
        $this->addSql('ALTER TABLE seance CHANGE date_heure date_heure DATETIME NOT NULL, CHANGE statut statut VARCHAR(255) DEFAULT \'prevue\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_de_paie DROP total_seances');
        $this->addSql('ALTER TABLE seance CHANGE date_heure date_heure DATE NOT NULL, CHANGE statut statut VARCHAR(255) NOT NULL');
    }
}
