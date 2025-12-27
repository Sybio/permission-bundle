<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Decision;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class PermissionDecisionFactory implements PermissionDecisionFactoryInterface
{
    public function createDecision(
        bool $granted,
        ConstraintViolationListInterface $violations,
    ): PermissionDecisionInterface {
        return new PermissionDecision(
            $granted,
            $violations,
        );
    }
}
