<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AchievementDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AchievementDefinition>
 *
 * @method AchievementDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchievementDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchievementDefinition[]    findAll()
 * @method AchievementDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchievementDefinition::class);
    }

    public function save(AchievementDefinition $award, bool $flush = true): void
    {
        $this->getEntityManager()->persist($award);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
