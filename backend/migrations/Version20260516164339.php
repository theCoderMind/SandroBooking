<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516164339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notizen (id BINARY(16) NOT NULL, date VARCHAR(10) NOT NULL, time VARCHAR(5) DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, color VARCHAR(20) DEFAULT \'default\' NOT NULL, done TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, tenant_id BINARY(16) NOT NULL, INDEX IDX_1EDD95F49033212A (tenant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notizen ADD CONSTRAINT FK_1EDD95F49033212A FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY `fk_reservation_resource`');
        $this->addSql('DROP INDEX idx_reservation_resource ON reservations');
        $this->addSql('ALTER TABLE reservations DROP resource_id');
        $this->addSql('ALTER TABLE special_closings RENAME INDEX fk_special_closings_tenant TO IDX_57EA91E29033212A');
        $this->addSql('ALTER TABLE staff CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notizen DROP FOREIGN KEY FK_1EDD95F49033212A');
        $this->addSql('DROP TABLE notizen');
        $this->addSql('ALTER TABLE reservations ADD resource_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT `fk_reservation_resource` FOREIGN KEY (resource_id) REFERENCES resources (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_reservation_resource ON reservations (resource_id)');
        $this->addSql('ALTER TABLE special_closings RENAME INDEX idx_57ea91e29033212a TO FK_special_closings_tenant');
        $this->addSql('ALTER TABLE staff CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
