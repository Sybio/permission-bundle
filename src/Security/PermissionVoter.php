<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Security;

use Sybio\PermissionBundle\PermissionInterface;
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string,PermissionInterface>
 */
final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly PermissionValidatorInterface $validator,
        private readonly string $securityAttribute,
    ) {
    }

    protected function supports(
        string $attribute,
        mixed $subject,
    ): bool {
        return $attribute === $this->securityAttribute
            && $subject instanceof PermissionInterface;
    }

    /**
     * @param class-string<PermissionInterface> $attribute
     * @param PermissionInterface $subject
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        $violations = $this->validator->validate($subject);

        return $violations->count() === 0;
    }
}
