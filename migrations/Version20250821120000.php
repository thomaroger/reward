<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250821120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout index composite sur historic(child_id, created_at)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_HISTORIC_CHILD_CREATED_AT ON historic (child_id, created_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_HISTORIC_CHILD_CREATED_AT ON historic');
    }
}


