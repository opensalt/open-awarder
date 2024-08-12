<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordFieldValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordField) {
            return;
        }

        if (null == $value) {
            return;
        }

        $asserts = 0;

        if (preg_match('/[A-Z]/', (string) $value, $matches)) {
            ++$asserts;
        }

        if (preg_match('/[a-z]/', (string) $value, $matches)) {
            ++$asserts;
        }

        if (preg_match('/\d/', (string) $value, $matches)) {
            ++$asserts;
        }

        if (preg_match('/[_\W]/', (string) $value, $matches)) {
            ++$asserts;
        }

        if ($asserts < 3) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
