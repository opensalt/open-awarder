<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AchievementDefinition;
use App\Entity\Award;
use App\Entity\Awarder;
use App\Entity\AwardTemplate;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Enums\ParticipantState;
use App\Form\Type\JsonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AwardType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'choice_label' => 'name',
            ])
            ->add('achievement', EntityType::class, [
                'class' => AchievementDefinition::class,
                'choice_label' => 'name',
            ])
            ->add('subject', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => 'email',
                'query_builder' => static fn($er) => $er->createQueryBuilder('p')
                    ->where('p.state = :state')
                    ->setParameter('state', ParticipantState::Active)
                    ->orderBy('p.email', 'ASC'),
            ])
            ->add('awardTemplate', EntityType::class, [
                'class' => AwardTemplate::class,
                'choice_label' => 'name',
            ])
            ->add('emailTemplate', EntityType::class, [
                'class' => EmailTemplate::class,
                'choice_label' => 'name',
            ])
            ->add('results', JsonType::class, [
                'required' => false,
            ])
            ->add('evidence', JsonType::class, [
                'required' => false,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Award::class,
        ]);
    }
}
