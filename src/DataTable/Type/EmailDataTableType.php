<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\Entity\Email;
use App\Enums\EmailState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Action\Type\ButtonActionType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Filter\Type\StringFilterType;
use Kreyu\Bundle\DataTableBundle\Bridge\Doctrine\Orm\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableBundle\Column\Type\DateTimeColumnType;
use Kreyu\Bundle\DataTableBundle\Column\Type\TextColumnType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailDataTableType extends AbstractDataTableType
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
            ->addColumn('subject', TextColumnType::class, [
                'label' => 'Subject',
                'property_path' =>'subject.email',
                'sort' => 'subject.email',
            ])
            ->addColumn('emailTemplate', TextColumnType::class, [
                'label' => 'Email Template',
                'property_path' => 'emailTemplate.name',
                'sort' => 'emailTemplate.name',
            ])
            ->addColumn('lastUpdated', DateTimeColumnType::class, [
                'label' => 'Last Updated',
                'property_path' => 'lastUpdated',
                'format' => 'Y-m-d H:i:s',
                'sort' => 'id',
                'value_attr' => ['class' => 'date-issued'],
            ])
            ->addColumn('status', TextColumnType::class, [
                'label' => 'State',
                'property_path' =>'status',
                'value_attr' => static function (string $state) : array {
                    $stateClass = 'state-'.preg_replace('/[^a-z0-9]/', '_', strtolower($state));
                    $bgClass = match ($state) {
                        EmailState::Pending->value => 'text-bg-warning bg-opacity-50 opacity-75',
                        EmailState::Ready->value => 'text-bg-info opacity-75',
                        EmailState::Sending->value => 'text-bg-light opacity-50',
                        EmailState::Sent->value => 'text-bg-success bg-opacity-75',
                        EmailState::Failed->value => 'text-bg-danger bg-opacity-50',
                        default => 'text-bg-secondary',
                    };
                    return [
                        'class' => implode(' ', ['badge', $stateClass, $bgClass]),
                    ];
                },
                'sort' => 'state_rank',
            ])
            ->addFilter('subject', StringFilterType::class, [
                'query_path' =>'subject.email',
            ])
            ->addFilter('emailTemplate', StringFilterType::class, [
                'query_path' =>'emailTemplate.name',
            ])
            ->addFilter('status', StringFilterType::class, [
                'label' => 'State',
                'form_type' => ChoiceType::class,
                'form_options' => [
                    'choices' => array_merge(...array_map(static fn(EmailState $state): array => [$state->name => $state->value], EmailState::cases())),
                    'multiple' => false,
                    'required' => false,
                ],
                'default_operator' => Operator::In,
            ])
            ->addRowAction('show', ButtonActionType::class, [
                'href' => fn(Email $award): string => $this->urlGenerator->generate('app_email_show', ['id' => $award->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            /*
            ->addRowAction('edit', ButtonActionType::class, [
                'href' => fn(Email $award): string => $this->urlGenerator->generate('app_email_edit', ['id' => $award->getId()]),
                //'visible' => static fn(Email $award): bool => $award->canEdit(),
                'visible' => false,
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            */
            /*
            ->addRowAction('publish', FormTokenActionType::class, [
                'action' => fn(Email $award): string => $this->urlGenerator->generate('app_award_publish', ['id' => $award->getId()]),
                'method' => 'POST',
                'visible' => static fn(Email $award): bool => $award->canPublish(),
                'token_id' => static fn(Email $award): string => 'publish'.$award->getId(),
                'label' => 'Publish',
                'button_attr' => ['class' => 'btn btn-secondary'],
                'attr' => ['class' => 'd-inline'],
            ])
            */
            ->setQuery(new DoctrineOrmProxyQuery(
                (new QueryBuilder($this->entityManager))
                    ->select('p, subject, emailTemplate')
                    ->addSelect("(CASE
                        WHEN p.status = 'Pending' THEN 1 
                        WHEN p.status = 'Ready' THEN 2
                        WHEN p.status = 'Sending' THEN 3 
                        WHEN p.status = 'Sent' THEN 4 
                        ELSE 99
                        END) AS HIDDEN state_rank")
                    ->from(Email::class, 'p')
                    ->leftJoin('p.subject', 'subject')
                    ->leftJoin('p.emailTemplate', 'emailTemplate')
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'lastUpdated' => 'desc',
            ]))
        ;
    }
}
