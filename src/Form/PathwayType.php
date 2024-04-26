<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AchievementDefinition;
use App\Entity\Pathway;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PathwayType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('finalCredential', EntityType::class, [
                'class' => AchievementDefinition::class,
                'choice_label' => 'name',
            ])
            ->add('emailTemplate', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                ],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pathway::class,
        ]);
    }
}
