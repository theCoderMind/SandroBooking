<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260513000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add table_number column to reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservations ADD table_number INT NULL AFTER party_size');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservations DROP COLUMN table_number');
    }
}
