<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<array<array-key, mixed>, string>
 */
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

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
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

        try {
            $modelData = json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException) {
            throw new TransformationFailedException('Invalid JSON');
        }

        return $modelData;
    }
}
