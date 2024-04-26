<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240425192411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add 2fa columns to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD totp_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD totp_enabled BOOLEAN DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP totp_secret');
        $this->addSql('ALTER TABLE "user" DROP totp_enabled');
    }
}
