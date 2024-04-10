<?php

namespace App\Form;

use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('template', TextareaType::class, [
                'attr' => ['rows' => 10],
            ])
            ->add('awarder', EntityType::class, [
                'class' => Awarder::class,
                'choice_label' => 'name',
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
        ]);
    }
}
