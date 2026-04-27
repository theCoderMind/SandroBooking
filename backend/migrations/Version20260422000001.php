<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260422000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add layout JSON to venues; add resource_id to reservations';
    }

    public function up(Schema $schema): void
    {
        // Restaurantplan (Tisch-Positionen, Wände) als JSON im Venue speichern
        $this->addSql('ALTER TABLE venues ADD COLUMN layout JSON NULL');

        // Optionale Tischzuweisung bei einer Reservierung (nur UI-relevant, nullable)
        $this->addSql('ALTER TABLE reservations ADD COLUMN resource_id BINARY(16) NULL');
        $this->addSql('ALTER TABLE reservations ADD CONSTRAINT fk_reservation_resource
            FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_reservation_resource ON reservations (resource_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservations DROP FOREIGN KEY fk_reservation_resource');
        $this->addSql('DROP INDEX idx_reservation_resource ON reservations');
        $this->addSql('ALTER TABLE reservations DROP COLUMN resource_id');
        $this->addSql('ALTER TABLE venues DROP COLUMN layout');
    }
}
