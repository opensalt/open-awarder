<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Pathway;
use App\Enums\ParticipantState;
use App\Repository\PathwayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function __construct(private readonly PathwayRepository $pathwayRepository)
    {
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pathways = $this->pathwayRepository->findAll();
        $choices = [];
        foreach ($pathways as $pathway) {
            $choices[$pathway->getName()] = $pathway->getName();
        }

        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email', EmailType::class)
            ->add('subscribedPathway', EntityType::class, [
                'class' => Pathway::class,
                'choice_label' => 'name',
            ])
            ->add('acceptedTerms')
            ->add('phone')
            ->add('aboutMe')
            ->add('state', EnumType::class, [
                'class' => ParticipantState::class,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
