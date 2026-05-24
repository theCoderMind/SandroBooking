<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add gasttypen and hinweise JSON columns to guests table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests ADD COLUMN gasttypen JSON NULL, ADD COLUMN hinweise JSON NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests DROP COLUMN gasttypen, DROP COLUMN hinweise');
    }
}
