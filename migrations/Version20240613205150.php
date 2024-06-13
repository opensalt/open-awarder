<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240613205150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email attachment file table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_attachment (id UUID NOT NULL, template_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, mimetype VARCHAR(255) DEFAULT NULL, original_name VARCHAR(255) DEFAULT NULL, dimensions VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D5EC2B645DA0FB8 ON email_attachment (template_id)');
        $this->addSql('COMMENT ON COLUMN email_attachment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email_attachment.template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email_attachment.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE email_attachment ADD CONSTRAINT FK_D5EC2B645DA0FB8 FOREIGN KEY (template_id) REFERENCES email_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE email_attachment DROP CONSTRAINT FK_D5EC2B645DA0FB8');
        $this->addSql('DROP TABLE email_attachment');
    }
}
