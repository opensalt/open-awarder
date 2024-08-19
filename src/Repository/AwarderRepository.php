<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Awarder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<Awarder>
 *
 * @method Awarder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Awarder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Awarder[]    findAll()
 * @method Awarder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwarderRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, Awarder> */
    private array $awarders = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Awarder::class);
    }

    public function getAwarderFromName(?string $awarder): ?Awarder
    {
        if (null === $awarder || '' === $awarder) {
            return null;
        }

        if (array_key_exists($awarder, $this->awarders)) {
            return $this->awarders[$awarder];
        }

        $this->awarders[$awarder] = $this->findOneBy(['name' => $awarder]);

        return $this->awarders[$awarder];
    }

    #[\Override]
    public function reset(): void
    {
        $this->awarders = [];
    }
}
