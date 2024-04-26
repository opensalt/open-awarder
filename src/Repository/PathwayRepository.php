<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Pathway;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pathway>
 *
 * @method Pathway|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pathway|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pathway[]    findAll()
 * @method Pathway[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PathwayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pathway::class);
    }
}
