<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Twig;

use Sybio\PermissionBundle\Decision\PermissionDecisionFactoryInterface;
use Sybio\PermissionBundle\Decision\PermissionDecisionInterface;
use Sybio\PermissionBundle\PermissionInterface;
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PermissionTwigFunction extends AbstractExtension
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly PermissionValidatorInterface $permissionValidator,
        private readonly PermissionDecisionFactoryInterface $permissionDecisionFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('permission', $this->permission(...)),
        ];
    }

    /**
     * @param class-string<PermissionInterface> $permissionClass
     */
    public function permission(
        string $permissionClass,
        mixed ...$arguments,
    ): PermissionDecisionInterface {
        $permission = new $permissionClass(...$arguments);

        $isGranted = $this->authorizationChecker->isGranted(
            $permissionClass,
            $permission,
        );
        $violations = $this->permissionValidator->getLastViolations();

        return $this->permissionDecisionFactory->createDecision(
            $isGranted,
            $violations,
        );
    }
}
