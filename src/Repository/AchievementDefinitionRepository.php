<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AchievementDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<AchievementDefinition>
 *
 * @method AchievementDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchievementDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchievementDefinition[]    findAll()
 * @method AchievementDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchievementDefinitionRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, AchievementDefinition> */
    private array $definitions = [];

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

    public function getAchievementDefinitionFromName(?string $achievement): ?AchievementDefinition
    {
        if (null === $achievement || '' === $achievement) {
            return null;
        }

        if (array_key_exists($achievement, $this->definitions)) {
            return $this->definitions[$achievement];
        }

        $this->definitions[$achievement] = $this->findOneBy(['name' => $achievement]);

        return $this->definitions[$achievement];
    }

    #[\Override]
    public function reset(): void
    {
        $this->definitions = [];
    }
}
