<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\TemplatePreview;
use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\Participant;
use App\Enums\ParticipantState;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplatePreviewType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            /*
            ->add('emailTemplate', EntityType::class, [
                'class' => EmailTemplate::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the email template to use',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            */
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => static fn(Participant $participant): string => $participant->getFirstName().' '.$participant->getLastName().' - '.$participant->getEmail(),
                'query_builder' => static fn($er) => $er->createQueryBuilder('p')
                    ->where('p.state = :state')
                    ->setParameter('state', ParticipantState::Active)
                    ->andWhere('p.acceptedTerms = true')
                    ->orderBy('p.lastName', 'ASC')
                    ->addOrderBy('p.firstName', 'ASC')
                    ->addOrderBy('p.email', 'ASC'),
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the person the award is to',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            ->add('achievement', EntityType::class, [
                'class' => AchievementDefinition::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the achievement being awarded',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the awarder',
                'attr' => ['onchange' => 'updatePreview()'],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TemplatePreview::class,
        ]);
    }
}
