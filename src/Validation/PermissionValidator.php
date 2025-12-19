<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Validation;

use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PermissionValidator implements PermissionValidatorInterface
{
    private ConstraintViolationListInterface $lastViolations;

    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
        $this->lastViolations = new ConstraintViolationList();
    }

    public function validate(PermissionInterface $permission): ConstraintViolationListInterface
    {
        $constraint = new PermissionConstraint();
        $this->lastViolations = $this->validator->validate(
            $permission,
            $constraint,
        );

        return $this->lastViolations;
    }

    public function getLastViolations(): ConstraintViolationListInterface
    {
        return $this->lastViolations;
    }
}
