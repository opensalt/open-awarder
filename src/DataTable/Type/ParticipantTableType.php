<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\Entity\Participant;
use App\Entity\Pathway;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\StringFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ParticipantTableType extends AbstractDataTableType
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
            ->addColumn('firstName', TextColumnType::class, [
                'label' => 'First Name',
                'property_path' => 'firstName',
                'sort' => 'firstName',
            ])
            ->addColumn('lastName', TextColumnType::class, [
                'label' => 'Last Name',
                'property_path' => 'lastName',
                'sort' => 'lastName',
            ])
            ->addColumn('email', TextColumnType::class, [
                'label' => 'Email',
                'property_path' => 'email',
                'sort' => 'email',
            ])
            ->addColumn('subscribedPathway', TextColumnType::class, [
                'label' => 'Subscribed Pathway',
                'property_path' => 'subscribedPathway.name',
                'sort' => 'pathway.name',
            ])
            ->addColumn('acceptedTerms', BooleanColumnType::class, [
                'label' => 'ToS',
                'header_attr' => [
                    'aria-label' => 'Accepted Terms',
                ],
                'label_true' => 'Yes',
                'label_false' => 'No',
                //'label_true' => '<i class="bi bi-check text-success"></i>',
                //'label_false' => '<i class="bi bi-x-octagon-fill text-danger"></i>',
                'property_path' => 'acceptedTerms',
                'sort' => 'acceptedTerms',
                'block_prefix' => 'check_or_x',
            ])
            ->addFilter('fullName', StringFilterType::class, [
                'label' => 'Full Name',
                'query_path' =>"CONCAT(a.firstName, ' ', a.lastName)",
                'lower' => true,
                'default_operator' => Operator::Contains,
            ])
            ->addFilter('email', StringFilterType::class, [
                'query_path' =>'email',
            ])
            ->addRowAction('show', ButtonActionType::class, [
                'href' => fn(Participant $participant): string => $this->urlGenerator->generate('app_participant_show', ['id' => $participant->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'href' => fn(Participant $participant): string => $this->urlGenerator->generate('app_participant_edit', ['id' => $participant->getId()]),
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->addFilter('subscribedPathway', EntityFilterType::class, [
                'form_options' => [
                    'class' => Pathway::class,
                    'query_builder' => fn(EntityRepository $er): QueryBuilder => $er->createQueryBuilder('a')
                        ->orderBy('a.name', 'ASC'),
                    'choice_label' => 'name',
                    //'multiple' => true,
                ],
                'choice_label' => 'name',
                'default_operator' => Operator::In,
                'query_path' => 'pathway.id',
            ])
            ->setQuery(new DoctrineOrmProxyQuery(
                (new QueryBuilder($this->entityManager))
                    ->select('a')
                    ->from(Participant::class, 'a')
                    ->leftJoin('a.subscribedPathway', 'pathway')
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'email' => 'asc',
            ]))
            ;
    }
}
