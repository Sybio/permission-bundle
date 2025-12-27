<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Twig;

use Sybio\PermissionBundle\PermissionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HasPermissionTwigFunction extends AbstractExtension
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_permission', $this->hasPermission(...)),
        ];
    }

    /**
     * @param class-string<PermissionInterface> $permissionClass
     */
    public function hasPermission(
        string $permissionClass,
        mixed ...$arguments,
    ): bool {
        $permission = new $permissionClass(...$arguments);

        return $this->authorizationChecker->isGranted(
            $permissionClass,
            $permission,
        );
    }
}
