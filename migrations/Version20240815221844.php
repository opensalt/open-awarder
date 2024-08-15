<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240815221844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email (id UUID NOT NULL, subject_id UUID NOT NULL, email_template_id UUID NOT NULL, award_id UUID DEFAULT NULL, email_from VARCHAR(255) NOT NULL, email_subject VARCHAR(255) NOT NULL, rendered_email TEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, last_updated TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E7927C7423EDC87 ON email (subject_id)');
        $this->addSql('CREATE INDEX IDX_E7927C74131A730F ON email (email_template_id)');
        $this->addSql('CREATE INDEX IDX_E7927C743D5282CF ON email (award_id)');
        $this->addSql('COMMENT ON COLUMN email.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email.subject_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email.email_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email.award_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email.last_updated IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C7423EDC87 FOREIGN KEY (subject_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C74131A730F FOREIGN KEY (email_template_id) REFERENCES email_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email ADD CONSTRAINT FK_E7927C743D5282CF FOREIGN KEY (award_id) REFERENCES award (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email DROP CONSTRAINT FK_E7927C7423EDC87');
        $this->addSql('ALTER TABLE email DROP CONSTRAINT FK_E7927C74131A730F');
        $this->addSql('ALTER TABLE email DROP CONSTRAINT FK_E7927C743D5282CF');
        $this->addSql('DROP TABLE email');
    }
}
