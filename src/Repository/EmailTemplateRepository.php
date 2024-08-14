<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EmailTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @extends ServiceEntityRepository<EmailTemplate>
 *
 * @method EmailTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailTemplate[]    findAll()
 * @method EmailTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailTemplateRepository extends ServiceEntityRepository implements ResetInterface
{
    /** @var array<array-key, EmailTemplate> */
    private array $templates = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailTemplate::class);
    }

    public function getTemplateFromName(?string $template): ?EmailTemplate
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
