<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AchievementDefinition;
use App\Entity\Award;
use App\Entity\Awarder;
use App\Entity\Participant;
use App\Enums\ParticipantState;
use App\Form\Type\JsonType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class AwardType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'choice_label' => 'name',
                'disabled' => true,
                'translation_domain' => false,
            ])
            ->add('achievement', EntityType::class, [
                'class' => AchievementDefinition::class,
                'choice_label' => 'name',
                'disabled' => true,
                'translation_domain' => false,
            ])
            ->add('subject', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => static fn(Participant $participant): string => $participant->getFirstName().' '.$participant->getLastName().' - '.$participant->getEmail(),
                'query_builder' => static fn($er) => $er->createQueryBuilder('p')
                    ->where('p.state = :state')
                    ->setParameter('state', ParticipantState::Active)
                    ->orderBy('p.email', 'ASC'),
                'disabled' => true,
                'translation_domain' => false,
            ])
            ->add('awardJson', JsonType::class, [
                'label' => 'Award JSON',
                'required' => true,
                'attr' => [
                    'rows' => 10,
                    'spellcheck' => 'false',
                ],
                'translation_domain' => false,
            ])
            ->add('emailTemplate', HiddenType::class, [
                'property_path' => 'emailTemplate?.id.toRfc4122',
                'required' => false,
                'disabled' => true,
                'translation_domain' => false,
            ])
            ->addDependent('awardEmail', ['emailTemplate'], static function (DependentField $field, ?string $emailTemplate): void {
                if ($emailTemplate === null || $emailTemplate === '') {
                    return;
                }

                $field->add(TextareaType::class, [
                    'label' => 'Email Body',
                    'required' => false,
                    'attr' => [
                        'rows' => 10,
                    ],
                    'translation_domain' => false,
                ]);
            })
            ->add('deleteFiles', ChoiceType::class, [
                'label' => 'Delete Evidence',
                'choices' => [],
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'translation_domain' => false,
            ])
            ->add('moreEvidence', FileType::class, [
                'label' => 'Add Evidence',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'translation_domain' => false,
            ])
        ;

        //$builder->get('deleteFiles')->resetViewTransformers();
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            $isDirty = false;
            $choices = $form->get('deleteFiles')->getConfig()->getOption('choices');
            $submittedOpts = $data['deleteFiles'] ?? [];
            foreach ($submittedOpts as $opt) {
                if (!\in_array($opt, $choices, true)) {
                    $choices[$opt] = $opt;
                    $isDirty = true;
                }
            }

            if ($isDirty) {
                $form->add('deleteFiles', ChoiceType::class, [
                    'choices' => $choices,
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'translation_domain' => false,
                ]);
            }
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Award::class,
        ]);
    }
}
