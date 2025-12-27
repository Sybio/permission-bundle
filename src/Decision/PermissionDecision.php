<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Decision;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final readonly class PermissionDecision implements PermissionDecisionInterface
{
    public function __construct(
        private bool $granted,
        private ConstraintViolationListInterface $violations,
    ) {
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
