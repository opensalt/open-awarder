<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240419182025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Session Table';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('
            CREATE TABLE app_session (
                sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
                sess_data BYTEA NOT NULL,
                sess_lifetime INTEGER NOT NULL,
                sess_time INTEGER NOT NULL
            );
        ');
        $this->addSql('
            CREATE INDEX session_sess_lifetime_idx ON app_session(sess_lifetime);
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
