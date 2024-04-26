<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Form\Type\JsonType;
use App\Repository\AwarderRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AchievementDefinitionType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('uri', TextType::class)
            ->add('definition', JsonType::class, [
                'attr' => [
                    'rows' => 10,
                    'spellcheck' => 'false',
                ],
            ])
            ->add('awarders', EntityType::class, [
                'placeholder' => 'Select awarders',
                'class' => Awarder::class,
                'query_builder' => static fn(AwarderRepository $er): \Doctrine\ORM\QueryBuilder => $er->createQueryBuilder('a')
                    ->orderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AchievementDefinition::class,
        ]);
    }
}
