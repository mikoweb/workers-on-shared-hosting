<?php

namespace App\Core\Application\Validation;

use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationUtils
{
    public static function validateData(mixed $data, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($data);

        if ($violations->count() > 0) {
            throw new ValidationFailedException($data, $violations);
        }
    }
}
