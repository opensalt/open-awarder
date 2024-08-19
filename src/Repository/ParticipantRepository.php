<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Participant;
use App\Enums\AwardState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, Participant> */
    private array $participants = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * @return array<array-key, string>
     */
    public function getAchievementsForParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.awards', 'a')
            ->join('a.achievement', 'ad')
            ->select('ad.identifier')
            ->andWhere('p.id = :subject')
            ->setParameter('subject', $participant->getId())
            ->andWhere('a.state <> :pending')
            ->setParameter('pending', AwardState::Pending)
            ->andWhere('a.state <> :revoked')
            ->setParameter('revoked', AwardState::Revoked)
            ->getQuery()
            ->getSingleColumnResult()
            ;
    }

    /**
     * @return iterable<array-key, Participant>
     */
    public function getParticipants(): iterable
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->getQuery()
            ->toIterable()
            ;
    }

    public function getParticipantFromEmail(?string $participant): ?Participant
    {
        if (null === $participant || '' === $participant) {
            return null;
        }

        if (array_key_exists($participant, $this->participants)) {
            return $this->participants[$participant];
        }

        $this->participants[$participant] = $this->findOneBy(['email' => $participant]);

        return $this->participants[$participant];
    }

    #[\Override]
    public function reset(): void
    {
        $this->participants = [];
    }
}
