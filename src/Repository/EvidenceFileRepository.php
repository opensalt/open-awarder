<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EvidenceFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvidenceFile>
 *
 * @method EvidenceFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvidenceFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvidenceFile[]    findAll()
 * @method EvidenceFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvidenceFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvidenceFile::class);
    }

//    /**
//     * @return EvidenceFile[] Returns an array of EvidenceFile objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EvidenceFile
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
