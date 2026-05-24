<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260517000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Gast-Profil: birthday, wedding_date, total_revenue_cents';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests ADD birthday DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE guests ADD wedding_date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE guests ADD total_revenue_cents INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE guests DROP birthday');
        $this->addSql('ALTER TABLE guests DROP wedding_date');
        $this->addSql('ALTER TABLE guests DROP total_revenue_cents');
    }
}
