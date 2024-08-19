<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Email;
use App\Enums\EmailState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Email>
 */
class EmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Email::class);
    }

    public function updateWorkflowStatus(Uuid $emailId, EmailState $state): void
    {
        $this->find($emailId)?->setState($state);
        $this->getEntityManager()->flush();
    }

    public function save(Email $email, bool $flush = true): void
    {
        $this->getEntityManager()->persist($email);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
