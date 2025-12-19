<?php

declare(strict_types=1);

namespace Symfony\Component\Validator\Exception;

namespace Sybio\PermissionBundle\Exception;

use RuntimeException;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\PermissionInterface;

class PermissionCheckerNotFoundException extends RuntimeException implements PermissionExceptionInterface
{
    public function __construct(
        PermissionInterface $permission,
    ) {
        parent::__construct(
            sprintf(
                'You need to implement (and register) the permission checker (%s) for permission "%s".',
                PermissionCheckerInterface::class,
                $permission::class,
            ),
        );
    }
}
