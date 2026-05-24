<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reminders JSON column to guests table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests ADD reminders JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests DROP COLUMN reminders');
    }
}
