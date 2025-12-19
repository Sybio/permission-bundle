<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Validation;

use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface PermissionValidatorInterface
{
    public function validate(PermissionInterface $permission): ConstraintViolationListInterface;

    public function getLastViolations(): ConstraintViolationListInterface;
}
