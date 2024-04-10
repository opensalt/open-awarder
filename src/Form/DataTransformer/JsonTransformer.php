<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JsonTransformer implements DataTransformerInterface
{

    /**
     * @inheritDoc
     */
    #[\Override]
    public function transform(mixed $value): mixed
    {
        if (empty($value)) {
            return '';
        }

        return json_encode($value);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function reverseTransform(mixed $value): mixed
    {
        if (empty($value)) {
            return [];
        }

        $modelData = json_decode((string) $value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new TransformationFailedException('Invalid JSON');
        }

        return $modelData;
    }
}
