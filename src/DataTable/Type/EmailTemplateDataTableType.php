<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\Entity\EmailTemplate;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\StringFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailTemplateDataTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'Name',
                'property_path' =>'name',
                'sort' => 'name',
            ])
            ->addFilter('name', StringFilterType::class, [
                'query_path' =>'name',
            ])
            ->addRowAction('show', ButtonActionType::class, [
                'href' => fn(EmailTemplate $template): string => $this->urlGenerator->generate('app_email_template_show', ['id' => $template->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'href' => fn(EmailTemplate $template): string => $this->urlGenerator->generate('app_email_template_edit', ['id' => $template->getId()]),
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->addRowAction('Send', ButtonActionType::class, [
                'href' => fn(EmailTemplate $template): string => $this->urlGenerator->generate('app_email_template_send', ['id' => $template->getId()]),
                'label' => 'Send',
                'attr' => ['class' => 'btn btn-outline-secondary'],
            ])
            ->setQuery(new DoctrineOrmProxyQuery(
                (new QueryBuilder($this->entityManager))
                    ->select('t')
                    ->from(EmailTemplate::class, 't')
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'name' => 'asc',
            ]))
        ;
    }
}
