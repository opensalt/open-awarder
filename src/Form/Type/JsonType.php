<?php

namespace App\Form\Type;

use App\Form\DataTransformer\JsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class JsonType extends AbstractType
{

    #[\Override]
    public function getParent(): ?string
    {
        return TextType::class;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new JsonTransformer());
    }
}
