<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Decision;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface PermissionDecisionFactoryInterface
{
    public function createDecision(
        bool $granted,
        ConstraintViolationListInterface $violations,
    ): PermissionDecisionInterface;
}
