<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Awarder;
use App\Entity\AwardTemplate;
use App\Form\Type\JsonType;
use App\Repository\AwarderRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AwardTemplateType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('template', JsonType::class, [
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('awarders', EntityType::class, [
                'placeholder' => 'Select awarders',
                'class' => Awarder::class,
                'query_builder' => static fn(AwarderRepository $er): QueryBuilder => $er->createQueryBuilder('a')
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
            'data_class' => AwardTemplate::class,
        ]);
    }
}
