<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @template T of PermissionInterface
 */
interface PermissionCheckerInterface
{
    /**
     * @param T $permission
     */
    public function check(
        PermissionInterface $permission,
        ExecutionContextInterface $context,
    ): void;

    /**
     * @return class-string<T>
     */
    public static function permissionClass(): string;
}
