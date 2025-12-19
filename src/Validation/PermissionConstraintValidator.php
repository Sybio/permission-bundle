<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Validation;

use Sybio\PermissionBundle\Exception\PermissionCheckerNotFoundException;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class PermissionConstraintValidator extends ConstraintValidator
{
    /**
     * @param ServiceLocator<PermissionCheckerInterface<PermissionInterface>> $permissionCheckerLocator
     */
    public function __construct(
        private readonly ServiceLocator $permissionCheckerLocator,
    ) {
    }

    public function validate(
        mixed $value,
        Constraint $constraint,
    ): void {
        if (!$constraint instanceof PermissionConstraint) {
            throw new UnexpectedTypeException($constraint, PermissionConstraint::class);
        }

        if (!$value instanceof PermissionInterface) {
            throw new UnexpectedValueException($value, PermissionInterface::class);
        }

        if (!$this->permissionCheckerLocator->has($value::class)) {
            throw new PermissionCheckerNotFoundException(
                $value,
            );
        }

        $checker = $this->permissionCheckerLocator->get($value::class);

        $checker->check(
            $value,
            $this->context,
        );
    }
}
