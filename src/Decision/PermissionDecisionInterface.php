<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Decision;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface PermissionDecisionInterface
{
    public function isGranted(): bool;

    public function getViolations(): ConstraintViolationListInterface;
}
