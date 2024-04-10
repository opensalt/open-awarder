<?php

namespace App\Repository;

use App\Entity\Awarder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Awarder>
 *
 * @method Awarder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Awarder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Awarder[]    findAll()
 * @method Awarder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwarderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Awarder::class);
    }

    //    /**
    //     * @return Awarder[] Returns an array of Awarder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Awarder
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
