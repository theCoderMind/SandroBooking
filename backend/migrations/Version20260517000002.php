<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cancellation_count, last_visit_at, allergies to guests table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests ADD cancellation_count INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE guests ADD last_visit_at DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE guests ADD allergies TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests DROP COLUMN cancellation_count');
        $this->addSql('ALTER TABLE guests DROP COLUMN last_visit_at');
        $this->addSql('ALTER TABLE guests DROP COLUMN allergies');
    }
}
