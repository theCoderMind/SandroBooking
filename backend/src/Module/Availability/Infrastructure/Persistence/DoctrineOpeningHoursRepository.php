<?php

declare(strict_types=1);

namespace App\Module\Availability\Infrastructure\Persistence;

use App\Module\Availability\Domain\Entity\OpeningHours;
use App\Module\Tenant\Domain\Entity\Tenant;
use Doctrine\DBAL\Connection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineOpeningHoursRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly Connection $dbal,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct($registry, OpeningHours::class);
    }

    /** @return OpeningHours[] */
    public function findByTenant(Tenant $tenant): array
    {
        $ids = $this->dbal->fetchFirstColumn(
            'SELECT BIN_TO_UUID(id) FROM opening_hours
              WHERE tenant_id = UNHEX(REPLACE(?, \'-\', \'\'))
              ORDER BY weekday ASC',
            [$tenant->getId()->toRfc4122()],
        );

        return array_values(array_filter(
            array_map(fn(string $id) => $this->em->find(OpeningHours::class, \Symfony\Component\Uid\Uuid::fromString($id)), $ids),
        ));
    }

    public function findByTenantAndWeekday(Tenant $tenant, int $weekday): ?OpeningHours
    {
        $id = $this->dbal->fetchOne(
            'SELECT BIN_TO_UUID(id) FROM opening_hours
              WHERE tenant_id = UNHEX(REPLACE(?, \'-\', \'\'))
                AND weekday = ?',
            [$tenant->getId()->toRfc4122(), $weekday],
        );

        if ($id === false) {
            return null;
        }

        return $this->em->find(OpeningHours::class, \Symfony\Component\Uid\Uuid::fromString($id));
    }

    public function save(OpeningHours $oh): void
    {
        $this->em->persist($oh);
        $this->em->flush();
    }

    public function saveAll(array $list): void
    {
        foreach ($list as $oh) {
            $this->em->persist($oh);
        }
        $this->em->flush();
    }
}
