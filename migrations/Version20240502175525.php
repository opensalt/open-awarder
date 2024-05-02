<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240502175525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add initial version of tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE achievement_definition (id UUID NOT NULL, name VARCHAR(255) NOT NULL, uri TEXT NOT NULL, identifier VARCHAR(255) DEFAULT NULL, definition JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64B5FB7F841CB121 ON achievement_definition (uri)');
        $this->addSql('COMMENT ON COLUMN achievement_definition.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE achievements_awarders (achievement_definition_id UUID NOT NULL, awarder_id UUID NOT NULL, PRIMARY KEY(achievement_definition_id, awarder_id))');
        $this->addSql('CREATE INDEX IDX_7625952175AF954C ON achievements_awarders (achievement_definition_id)');
        $this->addSql('CREATE INDEX IDX_76259521B45A0947 ON achievements_awarders (awarder_id)');
        $this->addSql('COMMENT ON COLUMN achievements_awarders.achievement_definition_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN achievements_awarders.awarder_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE award (id UUID NOT NULL, awarder_id UUID NOT NULL, subject_id UUID NOT NULL, achievement_id UUID NOT NULL, award_template_id UUID NOT NULL, email_template_id UUID DEFAULT NULL, results JSON DEFAULT NULL, state VARCHAR(255) NOT NULL, award_json JSON DEFAULT NULL, award_email TEXT DEFAULT NULL, award_email_from VARCHAR(255) DEFAULT NULL, award_email_subject VARCHAR(255) DEFAULT NULL, request_id VARCHAR(255) DEFAULT NULL, last_response JSON DEFAULT NULL, accept_url TEXT DEFAULT NULL, last_updated TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8A5B2EE7B45A0947 ON award (awarder_id)');
        $this->addSql('CREATE INDEX IDX_8A5B2EE723EDC87 ON award (subject_id)');
        $this->addSql('CREATE INDEX IDX_8A5B2EE7B3EC99FE ON award (achievement_id)');
        $this->addSql('CREATE INDEX IDX_8A5B2EE710F049E ON award (award_template_id)');
        $this->addSql('CREATE INDEX IDX_8A5B2EE7131A730F ON award (email_template_id)');
        $this->addSql('COMMENT ON COLUMN award.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.awarder_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.subject_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.achievement_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.award_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.email_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award.last_updated IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('CREATE TABLE award_template (id UUID NOT NULL, name VARCHAR(255) NOT NULL, template JSON NOT NULL, fields JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_95F24C565E237E06 ON award_template (name)');
        $this->addSql('COMMENT ON COLUMN award_template.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE award_template_awarders (award_template_id UUID NOT NULL, awarder_id UUID NOT NULL, PRIMARY KEY(award_template_id, awarder_id))');
        $this->addSql('CREATE INDEX IDX_D1BBAAEA10F049E ON award_template_awarders (award_template_id)');
        $this->addSql('CREATE INDEX IDX_D1BBAAEAB45A0947 ON award_template_awarders (awarder_id)');
        $this->addSql('COMMENT ON COLUMN award_template_awarders.award_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN award_template_awarders.awarder_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE awarder (id UUID NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, issuer_id TEXT NOT NULL, contact TEXT NOT NULL, protocol TEXT DEFAULT NULL, ocp_info JSON DEFAULT NULL, state VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2AB17FB25E237E06 ON awarder (name)');
        $this->addSql('COMMENT ON COLUMN awarder.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE email_template (id UUID NOT NULL, name VARCHAR(255) NOT NULL, from_address VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, template TEXT NOT NULL, fields JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN email_template.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE email_template_awarders (email_template_id UUID NOT NULL, awarder_id UUID NOT NULL, PRIMARY KEY(email_template_id, awarder_id))');
        $this->addSql('CREATE INDEX IDX_34168360131A730F ON email_template_awarders (email_template_id)');
        $this->addSql('CREATE INDEX IDX_34168360B45A0947 ON email_template_awarders (awarder_id)');
        $this->addSql('COMMENT ON COLUMN email_template_awarders.email_template_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN email_template_awarders.awarder_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE evidence_file (id UUID NOT NULL, award_id UUID DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, mimetype VARCHAR(255) DEFAULT NULL, original_name VARCHAR(255) DEFAULT NULL, dimensions VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_22008893D5282CF ON evidence_file (award_id)');
        $this->addSql('COMMENT ON COLUMN evidence_file.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN evidence_file.award_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN evidence_file.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE participant (id UUID NOT NULL, subscribed_pathway_id UUID DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, accepted_terms BOOLEAN DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, about_me TEXT DEFAULT NULL, state VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D79F6B112891EA6A ON participant (subscribed_pathway_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D79F6B11E7927C74 ON participant (email)');
        $this->addSql('COMMENT ON COLUMN participant.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN participant.subscribed_pathway_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE pathway (id UUID NOT NULL, final_credential_id UUID DEFAULT NULL, name VARCHAR(255) NOT NULL, email_template TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_44EDA7E2CA004938 ON pathway (final_credential_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_44EDA7E25E237E06 ON pathway (name)');
        $this->addSql('COMMENT ON COLUMN pathway.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN pathway.final_credential_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, totp_enabled BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE achievements_awarders ADD CONSTRAINT FK_7625952175AF954C FOREIGN KEY (achievement_definition_id) REFERENCES achievement_definition (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE achievements_awarders ADD CONSTRAINT FK_76259521B45A0947 FOREIGN KEY (awarder_id) REFERENCES awarder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE7B45A0947 FOREIGN KEY (awarder_id) REFERENCES awarder (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE723EDC87 FOREIGN KEY (subject_id) REFERENCES participant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE7B3EC99FE FOREIGN KEY (achievement_id) REFERENCES achievement_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE710F049E FOREIGN KEY (award_template_id) REFERENCES award_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award ADD CONSTRAINT FK_8A5B2EE7131A730F FOREIGN KEY (email_template_id) REFERENCES email_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award_template_awarders ADD CONSTRAINT FK_D1BBAAEA10F049E FOREIGN KEY (award_template_id) REFERENCES award_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE award_template_awarders ADD CONSTRAINT FK_D1BBAAEAB45A0947 FOREIGN KEY (awarder_id) REFERENCES awarder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template_awarders ADD CONSTRAINT FK_34168360131A730F FOREIGN KEY (email_template_id) REFERENCES email_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE email_template_awarders ADD CONSTRAINT FK_34168360B45A0947 FOREIGN KEY (awarder_id) REFERENCES awarder (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE evidence_file ADD CONSTRAINT FK_22008893D5282CF FOREIGN KEY (award_id) REFERENCES award (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B112891EA6A FOREIGN KEY (subscribed_pathway_id) REFERENCES pathway (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pathway ADD CONSTRAINT FK_44EDA7E2CA004938 FOREIGN KEY (final_credential_id) REFERENCES achievement_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE achievements_awarders DROP CONSTRAINT FK_7625952175AF954C');
        $this->addSql('ALTER TABLE achievements_awarders DROP CONSTRAINT FK_76259521B45A0947');
        $this->addSql('ALTER TABLE award DROP CONSTRAINT FK_8A5B2EE7B45A0947');
        $this->addSql('ALTER TABLE award DROP CONSTRAINT FK_8A5B2EE723EDC87');
        $this->addSql('ALTER TABLE award DROP CONSTRAINT FK_8A5B2EE7B3EC99FE');
        $this->addSql('ALTER TABLE award DROP CONSTRAINT FK_8A5B2EE710F049E');
        $this->addSql('ALTER TABLE award DROP CONSTRAINT FK_8A5B2EE7131A730F');
        $this->addSql('ALTER TABLE award_template_awarders DROP CONSTRAINT FK_D1BBAAEA10F049E');
        $this->addSql('ALTER TABLE award_template_awarders DROP CONSTRAINT FK_D1BBAAEAB45A0947');
        $this->addSql('ALTER TABLE email_template_awarders DROP CONSTRAINT FK_34168360131A730F');
        $this->addSql('ALTER TABLE email_template_awarders DROP CONSTRAINT FK_34168360B45A0947');
        $this->addSql('ALTER TABLE evidence_file DROP CONSTRAINT FK_22008893D5282CF');
        $this->addSql('ALTER TABLE participant DROP CONSTRAINT FK_D79F6B112891EA6A');
        $this->addSql('ALTER TABLE pathway DROP CONSTRAINT FK_44EDA7E2CA004938');
        $this->addSql('DROP TABLE achievement_definition');
        $this->addSql('DROP TABLE achievements_awarders');
        $this->addSql('DROP TABLE award');
        $this->addSql('DROP TABLE award_template');
        $this->addSql('DROP TABLE award_template_awarders');
        $this->addSql('DROP TABLE awarder');
        $this->addSql('DROP TABLE email_template');
        $this->addSql('DROP TABLE email_template_awarders');
        $this->addSql('DROP TABLE evidence_file');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE pathway');
        $this->addSql('DROP TABLE "user"');
    }
}
