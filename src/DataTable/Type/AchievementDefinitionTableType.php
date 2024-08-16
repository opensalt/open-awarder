<?php

namespace App\DataTable\Type;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\StringFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AchievementDefinitionTableType extends AbstractDataTableType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addColumn('name', TextColumnType::class, [
                'label' => 'name',
                'property_path' => 'name',
                'sort' => 'name',
            ])
            ->addColumn('uri', TextColumnType::class, [
                'label' => 'uri',
                'property_path' => 'uri',
                'sort' => 'uri',
            ])
            ->addFilter('name', StringFilterType::class, [
                'query_path' =>'name',
            ])
            ->addFilter('uri', StringFilterType::class, [
                'query_path' =>'uri',
            ])
            ->addRowAction('show', ButtonActionType::class, [
                'href' => fn(AchievementDefinition $definition): string => $this->urlGenerator->generate('app_achievement_definition_show', ['id' => $definition->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'href' => fn(AchievementDefinition $definition): string => $this->urlGenerator->generate('app_achievement_definition_edit', ['id' => $definition->getId()]),
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->addFilter('awarders', EntityFilterType::class, [
                'form_options' => [
                    'class' => Awarder::class,
                    'choice_label' => 'name',
                    //'multiple' => true,
                ],
                'choice_label' => 'name',
                'default_operator' => Operator::In,
                'query_path' => 'awarders.id',
            ])
            ->setQuery(new DoctrineOrmProxyQuery(
                (new QueryBuilder($this->entityManager))
                    ->select('a')
                    ->from(AchievementDefinition::class, 'a')
                    ->leftJoin('a.awarders', 'awarders')
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'name' => 'asc',
            ]))
            ;
    }
}
