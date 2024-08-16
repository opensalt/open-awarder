<?php

declare(strict_types=1);

namespace App\DataTable\Type;

use App\DataTable\Action\Type\FormTokenActionType;
use App\Entity\Award;
use App\Enums\AwardState;
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

class AwardDataTableType extends AbstractDataTableType
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
            ->addColumn('achievement', TextColumnType::class, [
                'label' => 'Achievement',
                'property_path' => 'achievement.name',
                'sort' => 'achievement.name',
            ])
            ->addColumn('issued', DateTimeColumnType::class, [
                'label' => 'Issued',
                'property_path' => 'dateIssued',
                'format' => 'Y-m-d H:i:s',
                'sort' => 'id',
                'value_attr' => ['class' => 'date-issued'],
            ])
            ->addColumn('state', TextColumnType::class, [
                'label' => 'State',
                'property_path' =>'state.value',
                'value_attr' => static function (string $state) : array {
                    $stateClass = 'state-'.preg_replace('/[^a-z0-9]/', '_', strtolower($state));
                    $bgClass = match ($state) {
                        AwardState::Pending->value => 'text-bg-warning bg-opacity-50 opacity-75',
                        AwardState::Publishing->value, AwardState::OcpProcessing->value, AwardState::OcpProcessed->value => 'text-bg-info opacity-75',
                        AwardState::Published->value, AwardState::Offered->value, AwardState::Accepted->value => 'text-bg-success bg-opacity-75',
                        AwardState::Revoking->value, AwardState::Revoked->value => 'text-bg-light opacity-50',
                        AwardState::Failed->value => 'text-bg-danger bg-opacity-50',
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
            ->addFilter('achievement', StringFilterType::class, [
                'query_path' =>'achievement.name',
            ])
            ->addFilter('state', StringFilterType::class, [
                'form_type' => ChoiceType::class,
                'form_options' => [
                    'choices' => array_merge(...array_map(static fn(AwardState $state): array => [$state->name => $state->value], AwardState::cases())),
                    'multiple' => false,
                    'required' => false,
                ],
                'default_operator' => Operator::In,
            ])
            ->addRowAction('show', ButtonActionType::class, [
                'href' => fn(Award $award): string => $this->urlGenerator->generate('app_award_show', ['id' => $award->getId()]),
                'label' => 'Show',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->addRowAction('edit', ButtonActionType::class, [
                'href' => fn(Award $award): string => $this->urlGenerator->generate('app_award_edit', ['id' => $award->getId()]),
                'visible' => static fn(Award $award): bool => $award->canEdit(),
                'label' => 'Edit',
                'attr' => ['class' => 'btn btn-secondary'],
            ])
            ->addRowAction('publish', FormTokenActionType::class, [
                'action' => fn(Award $award): string => $this->urlGenerator->generate('app_award_publish', ['id' => $award->getId()]),
                'method' => 'POST',
                'visible' => static fn(Award $award): bool => $award->canPublish(),
                'token_id' => static fn(Award $award): string => 'publish'.$award->getId(),
                'label' => 'Publish',
                'button_attr' => ['class' => 'btn btn-secondary'],
                'attr' => ['class' => 'd-inline'],
            ])
            ->addRowAction('revoke', FormTokenActionType::class, [
                'action' => fn(Award $award): string => $this->urlGenerator->generate('app_award_revoke', ['id' => $award->getId()]),
                'method' => 'POST',
                'token_id' => static fn(Award $award): string => 'revoke'.$award->getId(),
                'visible' => static fn(Award $award): bool => $award->canRevoke(),
                'label' => 'Revoke',
                'button_attr' => ['class' => 'btn btn-danger'],
                'confirmation' => [
                    'label_title' => 'Revoke award',
                    'label_description' => 'Are you sure you want to revoke this award?',
                    'type' => 'danger',
                ],
                'attr' => ['class' => 'd-inline'],
            ])
            /*
            ->addBatchAction('publish_many', FormActionType::class, [
                'action' => 'app_award_publish',
                'method' => 'POST',
                'label' => 'Publish',
                'visible' => fn(Award $award): bool => $award->canPublish(),
                'button_attr' => ['class' => 'btn btn-secondary'],
                'confirmation' => [
                    'label_title' => 'Publish Awards',
                    'label_description' => 'Are you sure you want to publish these awards?',
                    'label_confirm' => 'Confirm',
                    'label_cancel' => 'Cancel',
                    'type' => 'warning',
                ],
            ])
            */
            /*
            ->addBatchAction('delete_many', FormActionType::class, [
                'action' => 'app_award_delete',
                'method' => 'POST',
                'label' => 'Delete',
                'visible' => fn(Award $award): bool => $award->canDelete(),
                'button_attr' => ['class' => 'btn btn-danger'],
                'confirmation' => [
                    'label_title' => 'Delete Awards',
                    'label_description' => 'Are you sure you want to delete these awards?',
                    'label_confirm' => 'Yes',
                    'label_cancel' => 'Cancel',
                    'type' => 'danger',
                ],
            ])
            */
            /*
            ->addRowAction('delete', 'app_award_delete', [])
            */
            ->setQuery(new DoctrineOrmProxyQuery(
                (new QueryBuilder($this->entityManager))
                    ->select('p, subject, achievement')
                    ->addSelect("(CASE
                        WHEN p.state = 'Pending' THEN 1 
                        WHEN p.state = 'Publishing' THEN 2
                        WHEN p.state = 'OcpProcessing' THEN 3 
                        WHEN p.state = 'OcpProcessed' THEN 4 
                        WHEN p.state = 'Published' THEN 5
                        WHEN p.state = 'Offered' THEN 6
                        WHEN p.state = 'Accepted' THEN 7
                        WHEN p.state = 'Revoking' THEN 8
                        WHEN p.state = 'Revoked' THEN 9
                        WHEN p.state = 'Failed' THEN 10
                        ELSE 99
                        END) AS HIDDEN state_rank")
                    ->from(Award::class, 'p')
                    ->leftJoin('p.subject', 'subject')
                    ->leftJoin('p.achievement', 'achievement')
            ))
            ->setDefaultSortingData(SortingData::fromArray([
                'issued' => 'desc',
            ]))
        ;
    }
}
