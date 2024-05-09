<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\MakeAward;
use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\AwardTemplate;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Enums\ParticipantState;
use App\Form\Type\KeyValueType;
use App\Service\TwigVariables;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfonycasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;
use function Symfony\Component\String\u;

class MakeAwardForm extends AbstractType
{
    public function __construct(
        private readonly TwigVariables $twigVariables,
    ) {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                    ->addOrderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'required' => false,
                'translation_domain' => false,
                'placeholder' => 'Select the awarder',
            ])
            ->addDependent('achievement', 'awarder', static function (DependentField $field, ?Awarder $awarder) : void {
                if (!$awarder instanceof Awarder) {
                    return;
                }

                $field->add(EntityType::class, [
                    'class' => AchievementDefinition::class,
                    'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                        ->join('a.awarders', 'aw')
                        ->where('aw.id = :awarder')
                        ->setParameter('awarder', $awarder)
                        ->addOrderBy('a.name', 'ASC'),
                    'choice_label' => 'name',
                    'required' => false,
                    'translation_domain' => false,
                    'placeholder' => 'Select the achievement being awarded',
                ]);
            })
            ->addDependent('subject', 'achievement', static function (DependentField $field, ?AchievementDefinition $achievement) : void {
                if (!$achievement instanceof AchievementDefinition) {
                    return;
                }

                $field->add(EntityType::class, [
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
                ]);
            })
            ->addDependent('awardTemplate', ['awarder', 'subject'], static function (DependentField $field, ?Awarder $awarder, ?Participant $subject) : void {
                if (!$subject instanceof Participant) {
                    return;
                }

                $field->add(EntityType::class, [
                    'class' => AwardTemplate::class,
                    'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                        ->join('a.awarders', 'aw')
                        ->where('aw.id = :awarder')
                        ->setParameter('awarder', $awarder),
                    'choice_label' => 'name',
                    'required' => false,
                    'translation_domain' => false,
                    'placeholder' => 'Select the award template to use',
                ]);
            })
            ->addDependent('emailTemplate', ['awarder', 'awardTemplate'], static function (DependentField $field, ?Awarder $awarder, ?AwardTemplate $awardTemplate) : void {
                if (!$awardTemplate instanceof AwardTemplate) {
                    return;
                }

                $field->add(EntityType::class, [
                    'class' => EmailTemplate::class,
                    'query_builder' => static fn($er) => $er->createQueryBuilder('a')
                        ->join('a.awarders', 'aw')
                        ->where('aw.id = :awarder')
                        ->setParameter('awarder', $awarder),
                    'choice_label' => 'name',
                    'required' => false,
                    'translation_domain' => false,
                    'placeholder' => 'Select the email template to use (if an email should be sent)',
                ]);
            })
            ->addDependent('vars', ['awardTemplate', 'emailTemplate', 'achievement'], function (
                DependentField $field,
                ?AwardTemplate $awardTemplate,
                ?EmailTemplate $emailTemplate,
                ?AchievementDefinition $achievement,
            ) use ($builder): void {
                if (!$awardTemplate instanceof AwardTemplate) {
                    return;
                }

                $awardVars = $this->twigVariables->getVariables(json_encode($awardTemplate->getTemplate(), JSON_THROW_ON_ERROR));
                $emailVars = $this->twigVariables->getVariables($emailTemplate?->getTemplate());
                $achievementVars = $this->twigVariables->getVariables($achievement->getDefinitionString());
                $resultDescriptions = ($achievement->getDefinition() ?? [])['resultDescriptions'] ?? [];
                foreach ($resultDescriptions as $resultDescription) {
                    if (null !== ($resultDescription['name'] ?? null)) {
                        $achievementVars[u($resultDescription['name'])->camel()->title()->toString()] = null;
                    }
                }

                $vars = array_keys(array_merge($awardVars, $emailVars, $achievementVars));

                $field->add(KeyValueType::class, [
                    'label' => 'Template Variables',
                    'keys' => $vars,
                    'translation_domain' => false,
                ]);
            })
            ->addDependent('evidence', ['awardTemplate', 'emailTemplate'], static function (DependentField $field, ?AwardTemplate $awardTemplate, ?EmailTemplate $emailTemplate): void {
                if (!$awardTemplate instanceof AwardTemplate) {
                    return;
                }

                $field->add( FileType::class, [
                    'label' => 'Evidence',
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'translation_domain' => false,
                ]);
            })
        ;

        $builder
            ->addDependent('submit', ['awardTemplate', 'emailTemplate'], static function (DependentField $field, ?AwardTemplate $awardTemplate, ?EmailTemplate $emailTemplate) : void {
                if (!$awardTemplate instanceof AwardTemplate) {
                    return;
                }

                $field->add(SubmitType::class, [
                    'label' => 'Submit Award',
                    'attr' => [
                        'class' => 'btn btn-primary float-end',
                    ],
                    'translation_domain' => false,
                ]);
            })
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MakeAward::class,
        ]);
    }
}
