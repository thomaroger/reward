<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250820120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des colonnes description et points sur historic pour ajustements manuels';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE historic ADD description LONGTEXT DEFAULT NULL, ADD points INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE historic DROP description, DROP points');
    }
}


