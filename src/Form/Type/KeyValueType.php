<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeyValueType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['keys'] as $key) {
            if (in_array($key, ['awarder', 'achievement', 'subject', 'context'])) {
                continue;
            }

            $builder->add($key, TextareaType::class, [
                'label' => $key,
                'required' => true,
                'translation_domain' => false,
            ]);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'keys' => [],
        ]);
    }
}
