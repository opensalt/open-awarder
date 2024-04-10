<?php

namespace App\Form;

use App\Entity\AchievementDefinition;
use App\Entity\Pathway;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
