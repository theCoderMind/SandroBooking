<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move opening_hours and special_closings from per-venue to per-tenant (global)';
    }

    public function up(Schema $schema): void
    {
        // ── opening_hours ─────────────────────────────────────────────────────

        // 1. tenant_id hinzufügen falls noch nicht vorhanden
        if (!$this->columnExists('opening_hours', 'tenant_id')) {
            $this->addSql('ALTER TABLE opening_hours ADD COLUMN tenant_id BINARY(16) NULL AFTER id');
            $this->addSql(
                'UPDATE opening_hours oh
                   JOIN venues v ON oh.venue_id = v.id
                  SET oh.tenant_id = v.tenant_id'
            );
            $this->addSql('ALTER TABLE opening_hours MODIFY COLUMN tenant_id BINARY(16) NOT NULL');
        }

        // 2. Duplikate entfernen (idempotent — schadet nicht wenn schon sauber)
        if ($this->columnExists('opening_hours', 'venue_id')) {
            $this->addSql(
                'DELETE oh FROM opening_hours oh
                   INNER JOIN opening_hours oh2
                     ON oh.tenant_id = oh2.tenant_id
                    AND oh.weekday   = oh2.weekday
                    AND oh.id        < oh2.id'
            );

            // 3. Alle FKs auf venue_id dynamisch ermitteln und droppen
            $fks = $this->getForeignKeys('opening_hours', 'venue_id');
            foreach ($fks as $fk) {
                $this->addSql("ALTER TABLE opening_hours DROP FOREIGN KEY `{$fk}`");
            }

            // 4. Unique-Index auf venue_id droppen falls vorhanden
            $indexes = $this->getIndexesOnColumn('opening_hours', 'venue_id');
            foreach ($indexes as $idx) {
                $this->addSql("ALTER TABLE opening_hours DROP INDEX `{$idx}`");
            }

            // 5. venue_id droppen
            $this->addSql('ALTER TABLE opening_hours DROP COLUMN venue_id');
        }

        // 6. Unique constraint auf tenant+weekday (falls noch nicht da)
        if (!$this->indexExists('opening_hours', 'uq_tenant_weekday')) {
            $this->addSql('ALTER TABLE opening_hours ADD UNIQUE KEY uq_tenant_weekday (tenant_id, weekday)');
        }

        // 7. FK tenant_id → tenants.id (falls noch nicht da)
        if (!$this->foreignKeyExists('opening_hours', 'FK_opening_hours_tenant')) {
            $this->addSql(
                'ALTER TABLE opening_hours
                   ADD CONSTRAINT FK_opening_hours_tenant
                   FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE'
            );
        }

        // ── special_closings ──────────────────────────────────────────────────

        // 1. tenant_id hinzufügen falls noch nicht vorhanden
        if (!$this->columnExists('special_closings', 'tenant_id')) {
            $this->addSql('ALTER TABLE special_closings ADD COLUMN tenant_id BINARY(16) NULL AFTER id');
            $this->addSql(
                'UPDATE special_closings sc
                   JOIN venues v ON sc.venue_id = v.id
                  SET sc.tenant_id = v.tenant_id'
            );
            $this->addSql('ALTER TABLE special_closings MODIFY COLUMN tenant_id BINARY(16) NOT NULL');
        }

        // 2. venue_id und dessen FK entfernen falls noch vorhanden
        if ($this->columnExists('special_closings', 'venue_id')) {
            $fks = $this->getForeignKeys('special_closings', 'venue_id');
            foreach ($fks as $fk) {
                $this->addSql("ALTER TABLE special_closings DROP FOREIGN KEY `{$fk}`");
            }
            $this->addSql('ALTER TABLE special_closings DROP COLUMN venue_id');
        }

        // 3. FK tenant_id → tenants.id (falls noch nicht da)
        if (!$this->foreignKeyExists('special_closings', 'FK_special_closings_tenant')) {
            $this->addSql(
                'ALTER TABLE special_closings
                   ADD CONSTRAINT FK_special_closings_tenant
                   FOREIGN KEY (tenant_id) REFERENCES tenants (id) ON DELETE CASCADE'
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE special_closings DROP FOREIGN KEY FK_special_closings_tenant');
        $this->addSql('ALTER TABLE special_closings ADD COLUMN venue_id BINARY(16) NULL');
        $this->addSql('ALTER TABLE special_closings DROP COLUMN tenant_id');

        $this->addSql('ALTER TABLE opening_hours DROP FOREIGN KEY FK_opening_hours_tenant');
        $this->addSql('ALTER TABLE opening_hours DROP INDEX uq_tenant_weekday');
        $this->addSql('ALTER TABLE opening_hours ADD COLUMN venue_id BINARY(16) NULL');
        $this->addSql('ALTER TABLE opening_hours DROP COLUMN tenant_id');
        $this->addSql('ALTER TABLE opening_hours ADD UNIQUE KEY uq_venue_weekday (venue_id, weekday)');
    }

    // ── Hilfsmethoden ─────────────────────────────────────────────────────────

    private function columnExists(string $table, string $column): bool
    {
        $result = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = ?
                AND COLUMN_NAME  = ?",
            [$table, $column]
        );
        return (int) $result > 0;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = ?
                AND INDEX_NAME   = ?",
            [$table, $indexName]
        );
        return (int) $result > 0;
    }

    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $result = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA      = DATABASE()
                AND TABLE_NAME        = ?
                AND CONSTRAINT_NAME   = ?
                AND CONSTRAINT_TYPE   = 'FOREIGN KEY'",
            [$table, $fkName]
        );
        return (int) $result > 0;
    }

    /** Gibt alle FK-Namen zurück die auf eine bestimmte Spalte zeigen */
    private function getForeignKeys(string $table, string $column): array
    {
        return $this->connection->fetchFirstColumn(
            "SELECT CONSTRAINT_NAME
               FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA  = DATABASE()
                AND TABLE_NAME    = ?
                AND COLUMN_NAME   = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );
    }

    /** Gibt alle Index-Namen zurück die eine bestimmte Spalte enthalten */
    private function getIndexesOnColumn(string $table, string $column): array
    {
        return $this->connection->fetchFirstColumn(
            "SELECT DISTINCT INDEX_NAME
               FROM INFORMATION_SCHEMA.STATISTICS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   = ?
                AND COLUMN_NAME  = ?
                AND INDEX_NAME  != 'PRIMARY'",
            [$table, $column]
        );
    }
}
