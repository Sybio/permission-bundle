<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Tests\Unit\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sybio\PermissionBundle\PermissionInterface;
use Sybio\PermissionBundle\Validation\PermissionConstraint;
use Sybio\PermissionBundle\Validation\PermissionValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(PermissionValidator::class)]
final class PermissionValidatorTest extends TestCase
{
    private ValidatorInterface&MockObject $validator;
    private PermissionValidator $permissionValidator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->permissionValidator = new PermissionValidator($this->validator);
    }

    #[Test]
    public function it_validates_permission_with_permission_constraint(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList();

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with(
                $permission,
                $this->isInstanceOf(PermissionConstraint::class),
            )
            ->willReturn($violations);

        $result = $this->permissionValidator->validate($permission);

        $this->assertSame($violations, $result);
    }

    #[Test]
    public function it_returns_empty_violations_when_validation_passes(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList();

        $this->validator
            ->method('validate')
            ->willReturn($violations);

        $result = $this->permissionValidator->validate($permission);

        $this->assertSame(0, $result->count());
        $this->assertSame($violations, $result);
    }

    #[Test]
    public function it_returns_violations_when_validation_fails(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
        ]);

        $this->validator
            ->method('validate')
            ->willReturn($violations);

        $result = $this->permissionValidator->validate($permission);

        $this->assertSame(1, $result->count());
        $this->assertSame($violations, $result);
    }

    #[Test]
    public function it_stores_last_violations_after_validation(): void
    {
        $permission = $this->createMock(PermissionInterface::class);
        $violations = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
        ]);

        $this->validator
            ->method('validate')
            ->willReturn($violations);

        $this->permissionValidator->validate($permission);

        $this->assertSame($violations, $this->permissionValidator->getLastViolations());
    }

    #[Test]
    public function it_updates_last_violations_on_each_validation(): void
    {
        $permission1 = $this->createMock(PermissionInterface::class);
        $permission2 = $this->createMock(PermissionInterface::class);
        $violations1 = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
        ]);
        $violations2 = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
            $this->createMock(ConstraintViolation::class),
        ]);

        $this->validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->willReturnOnConsecutiveCalls($violations1, $violations2);

        $this->permissionValidator->validate($permission1);
        $this->assertSame(1, $this->permissionValidator->getLastViolations()->count());

        $this->permissionValidator->validate($permission2);
        $this->assertSame(2, $this->permissionValidator->getLastViolations()->count());
    }

    #[Test]
    public function it_returns_empty_violations_initially(): void
    {
        $lastViolations = $this->permissionValidator->getLastViolations();

        $this->assertInstanceOf(ConstraintViolationListInterface::class, $lastViolations);
        $this->assertSame(0, $lastViolations->count());
    }
}
