<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create staff table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE staff (
                id            BINARY(16)   NOT NULL,
                tenant_id     BINARY(16)   NOT NULL,
                employee_number SMALLINT UNSIGNED NOT NULL,
                name          VARCHAR(255) NOT NULL,
                email         VARCHAR(255) DEFAULT NULL,
                role          VARCHAR(20)  NOT NULL DEFAULT \'staff\',
                active        TINYINT(1)   NOT NULL DEFAULT 1,
                created_at    DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                updated_at    DATETIME     NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY (id),
                UNIQUE KEY uq_staff_tenant_number (tenant_id, employee_number),
                CONSTRAINT fk_staff_tenant FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE staff');
    }
}
