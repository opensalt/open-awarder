<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240506154349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fields column to achievement_definition';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE achievement_definition ADD fields JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE achievement_definition DROP fields');
    }
}
