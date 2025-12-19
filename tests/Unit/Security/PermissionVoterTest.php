<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Tests\Unit\Security;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Sybio\PermissionBundle\PermissionInterface;
use Sybio\PermissionBundle\Security\PermissionVoter;
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

#[CoversClass(PermissionVoter::class)]
final class PermissionVoterTest extends TestCase
{
    private PermissionValidatorInterface&MockObject $validator;
    private string $securityAttribute;
    private PermissionVoter $voter;
    private TokenInterface&MockObject $token;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(PermissionValidatorInterface::class);
        $this->securityAttribute = 'PERMISSION_CHECK';
        $this->voter = new PermissionVoter($this->validator, $this->securityAttribute);
        $this->token = $this->createMock(TokenInterface::class);
    }

    #[Test]
    public function it_supports_when_attribute_matches_and_subject_is_permission_interface(): void
    {
        $permission = $this->createMock(PermissionInterface::class);

        $result = $this->voter->vote($this->token, $permission, [$this->securityAttribute]);

        $this->assertSame(1, $result);
    }

    #[Test]
    public function it_does_not_support_when_attribute_does_not_match(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $differentAttribute = 'DIFFERENT_ATTRIBUTE';

        $result = $this->voter->vote($this->token, $permission, [$differentAttribute]);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function it_does_not_support_when_subject_is_not_permission_interface(): void
    {
        $notAPermission = new stdClass();

        $result = $this->voter->vote($this->token, $notAPermission, [$this->securityAttribute]);

        $this->assertSame(0, $result);
    }

    #[Test]
    public function it_grants_access_when_there_are_no_violations(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList();

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($permission)
            ->willReturn($violations);

        $result = $this->voter->vote($this->token, $permission, [$this->securityAttribute]);

        $this->assertSame(1, $result);
    }

    #[Test]
    public function it_denies_access_when_there_are_violations(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($permission)
            ->willReturn($violations);

        $result = $this->voter->vote($this->token, $permission, [$this->securityAttribute]);

        $this->assertSame(-1, $result);
    }
}
