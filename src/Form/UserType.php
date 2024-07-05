<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $roleChoices = [];
        $roleChoices['Admin'] = 'ROLE_ADMIN';
        //$roleChoices['User'] = 'ROLE_USER';

        $builder
            ->add('username')
            ->add('plainPassword', TextType::class, [
                'required' => in_array('registration', $options['validation_groups'] ?? [], true),
                'label' => 'Password',
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $roleChoices,
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
