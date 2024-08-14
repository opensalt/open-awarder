<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pathway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<Pathway>
 *
 * @method Pathway|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pathway|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pathway[]    findAll()
 * @method Pathway[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PathwayRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, Pathway> */
    private array $pathways = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pathway::class);
    }

    public function getPathwayFromName(?string $name): ?Pathway
    {
        if (null === $name || '' === $name) {
            return null;
        }

        if (array_key_exists($name, $this->pathways)) {
            return $this->pathways[$name];
        }

        $this->pathways[$name] = $this->findOneBy(['name' => $name]);

        return $this->pathways[$name];
    }

    public function reset(): void
    {
        $this->pathways = [];
    }
}
