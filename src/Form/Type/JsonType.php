<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Form\DataTransformer\JsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class JsonType extends AbstractType
{

    #[\Override]
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new JsonTransformer());
    }
}
