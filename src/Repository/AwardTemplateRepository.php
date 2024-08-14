<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AwardTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<AwardTemplate>
 *
 * @method AwardTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method AwardTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method AwardTemplate[]    findAll()
 * @method AwardTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardTemplateRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, AwardTemplate> */
    private array $templates = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardTemplate::class);
    }

    public function getTemplateFromName(?string $template): ?AwardTemplate
    {
        if (null === $template || '' === $template) {
            return null;
        }

        if (array_key_exists($template, $this->templates)) {
            return $this->templates[$template];
        }

        $this->templates[$template] = $this->findOneBy(['name' => $template]);

        return $this->templates[$template];
    }

    public function reset(): void
    {
        $this->templates = [];
    }
}
