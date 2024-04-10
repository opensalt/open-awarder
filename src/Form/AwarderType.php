<?php

namespace App\Form;

use App\Entity\Awarder;
use App\Enums\AwarderState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AwarderType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('issuerId')
            ->add('contact')
            ->add('protocol', null, [
                'required' => false,
            ])
            ->add('ocpInfo')
            ->add('state', EnumType::class, [
                'class' => AwarderState::class,
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Awarder::class,
        ]);
    }
}
