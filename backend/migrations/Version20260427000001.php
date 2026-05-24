<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260427000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ui_settings JSON column to tenants';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tenants ADD COLUMN ui_settings JSON NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tenants DROP COLUMN ui_settings');
    }
}
