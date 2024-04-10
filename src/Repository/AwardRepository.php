<?php

namespace App\Repository;

use App\Entity\Award;
use App\Enums\AwardState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Award>
 *
 * @method Award|null find($id, $lockMode = null, $lockVersion = null)
 * @method Award|null findOneBy(array $criteria, array $orderBy = null)
 * @method Award[]    findAll()
 * @method Award[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Award::class);
    }

    public function updateWorkflowStatus(Uuid $awardId, AwardState $state): void
    {
        $this->find($awardId)?->setState($state);
        $this->getEntityManager()->flush();
    }
}
