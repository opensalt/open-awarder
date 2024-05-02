<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\BooleanColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TemplateColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ParticipantDataTableType extends AbstractDataTableType
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
            ->addColumn('firstName')
            ->addColumn('lastName')
            ->addColumn('email')
            ->addColumn('subscribedPathway', TextColumnType::class, [
                'label' => 'Pathway',
            ])
            ->addColumn('acceptedTerms', BooleanColumnType::class)
            ->addColumn('state', TemplateColumnType::class, [
                'template_path' => 'participant/state.html.twig',
            ])
            ->addRowAction('Show', ButtonActionType::class, [
                'href' => fn(Participant $participant): string => $this->urlGenerator->generate('app_participant_show', ['id' => $participant->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addRowAction('Edit', ButtonActionType::class, [
                'href' => fn(Participant $participant): string => $this->urlGenerator->generate('app_participant_edit', ['id' => $participant->getId()]),
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->addAction('create', ButtonActionType::class, [
                'href' => $this->urlGenerator->generate('app_participant_new'),
                'label' => 'Add participant',
            ])
            ->addBatchAction('Delete', ButtonActionType::class, [
                'href' => $this->urlGenerator->generate('app_participant_new'),
                    'confirmation' => [
                        'translation_domain' => 'KreyuDataTable',
                        'label_title' => 'Action confirmation',
                        'label_description' => 'Are you sure you want to execute this action?',
                        'label_confirm' => 'Confirm',
                        'label_cancel' => 'Cancel',
                        'type' => 'danger', // "danger", "warning" or "info"
                    ],
            ])
            ->setQuery(new DoctrineOrmProxyQuery((new QueryBuilder($this->entityManager))->select('p')->from(Participant::class, 'p')));
            ;
    }
}
