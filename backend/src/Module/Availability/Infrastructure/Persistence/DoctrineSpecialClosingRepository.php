<?php

declare(strict_types=1);

namespace App\Module\Availability\Infrastructure\Persistence;

use App\Module\Availability\Domain\Entity\SpecialClosing;
use App\Module\Tenant\Domain\Entity\Tenant;
use Doctrine\DBAL\Connection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class DoctrineSpecialClosingRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $dbal,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct($registry, SpecialClosing::class);
    }

    /** @return SpecialClosing[] */
    public function findByTenant(Tenant $tenant): array
    {
        $tenantUid = $tenant->getId()->toRfc4122();

        $ids = $this->dbal->fetchFirstColumn(
            'SELECT BIN_TO_UUID(id) FROM special_closings
              WHERE tenant_id = UNHEX(REPLACE(?, \'-\', \'\'))
              ORDER BY date_from ASC',
            [$tenantUid],
        );

        return array_values(array_filter(
            array_map(fn(string $id) => $this->em->find(SpecialClosing::class, Uuid::fromString($id)), $ids),
        ));
    }

    public function findById(Uuid $id): ?SpecialClosing
    {
        return $this->em->find(SpecialClosing::class, $id);
    }

    /**
     * Alle Sondertage die ein bestimmtes Datum betreffen.
     * @return SpecialClosing[]
     */
    public function findAffectingDate(Tenant $tenant, \DateTimeImmutable $date): array
    {
        $tenantUid = $tenant->getId()->toRfc4122();
        $dateStr   = $date->format('Y-m-d');

        $ids = $this->dbal->fetchFirstColumn(
            'SELECT BIN_TO_UUID(id) FROM special_closings
              WHERE tenant_id = UNHEX(REPLACE(?, \'-\', \'\'))
                AND date_from <= ?
                AND date_to   >= ?
              ORDER BY date_from ASC',
            [$tenantUid, $dateStr, $dateStr],
        );

        return array_values(array_filter(
            array_map(fn(string $id) => $this->em->find(SpecialClosing::class, Uuid::fromString($id)), $ids),
        ));
    }

    public function save(SpecialClosing $sc): void
    {
        $this->em->persist($sc);
        $this->em->flush();
    }

    public function delete(SpecialClosing $sc): void
    {
        $this->em->remove($sc);
        $this->em->flush();
    }
}
