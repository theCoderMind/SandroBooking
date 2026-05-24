<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260429000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create opening_hours and special_closings tables';
    }

    public function up(Schema $schema): void
    {
        // CREATE TABLE IF NOT EXISTS macht die Migration idempotent —
        // falls eine der Tabellen schon manuell oder durch einen früheren
        // (teilweise gescheiterten) Lauf angelegt wurde, schlägt die
        // Migration nicht mehr fehl.
        $this->addSql('
            CREATE TABLE IF NOT EXISTS opening_hours (
                id          BINARY(16)   NOT NULL,
                venue_id    BINARY(16)   NOT NULL,
                weekday     SMALLINT     NOT NULL,
                open        TINYINT(1)   NOT NULL DEFAULT 1,
                open_time   VARCHAR(5)   NULL,
                close_time  VARCHAR(5)   NULL,
                breaks      JSON         NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_venue_weekday (venue_id, weekday),
                CONSTRAINT fk_oh_venue FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS special_closings (
                id          BINARY(16)   NOT NULL,
                venue_id    BINARY(16)   NOT NULL,
                date_from   DATE         NOT NULL,
                date_to     DATE         NOT NULL,
                reason      VARCHAR(255) NULL,
                full_day    TINYINT(1)   NOT NULL DEFAULT 1,
                open_time   VARCHAR(5)   NULL,
                close_time  VARCHAR(5)   NULL,
                created_at  DATETIME     NOT NULL,
                PRIMARY KEY (id),
                CONSTRAINT fk_sc_venue FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS special_closings');
        $this->addSql('DROP TABLE IF EXISTS opening_hours');
    }
}
