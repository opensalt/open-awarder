<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AwardTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AwardTemplate>
 *
 * @method AwardTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method AwardTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method AwardTemplate[]    findAll()
 * @method AwardTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardTemplate::class);
    }
}
