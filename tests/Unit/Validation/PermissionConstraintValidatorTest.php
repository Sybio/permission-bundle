<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\Tests\Unit\Validation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sybio\PermissionBundle\Exception\PermissionCheckerNotFoundException;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\PermissionInterface;
use Sybio\PermissionBundle\Validation\PermissionConstraint;
use Sybio\PermissionBundle\Validation\PermissionConstraintValidator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

#[CoversClass(PermissionConstraintValidator::class)]
final class PermissionConstraintValidatorTest extends TestCase
{
    /**
     * @var ServiceLocator<PermissionCheckerInterface<PermissionInterface>>&MockObject
     */
    private ServiceLocator $permissionCheckerLocator;
    private ExecutionContextInterface&MockObject $executionContext;
    private PermissionConstraintValidator $validator;

    protected function setUp(): void
    {
        $this->permissionCheckerLocator = $this->createMock(ServiceLocator::class);
        $this->executionContext = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new PermissionConstraintValidator($this->permissionCheckerLocator);
        $this->validator->initialize($this->executionContext);
    }

    #[Test]
    public function it_throws_unexpected_type_exception_when_constraint_is_not_permission_constraint(): void
    {
        $constraint = $this->createMock(Constraint::class);
        $value = $this->createMock(PermissionInterface::class);

        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Expected argument of type "%s", "%s" given',
                PermissionConstraint::class,
                $constraint::class,
            ),
        );

        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function it_throws_unexpected_value_exception_when_value_is_not_permission_interface(): void
    {
        $constraint = new PermissionConstraint();
        $value = 'not-a-permission';

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Expected argument of type "%s", "string" given',
                PermissionInterface::class,
            ),
        );

        $this->validator->validate($value, $constraint);
    }

    #[Test]
    public function it_throws_permission_checker_not_found_exception_when_checker_is_not_registered(): void
    {
        $constraint = new PermissionConstraint();
        $permission = $this->createMock(PermissionInterface::class);

        $this->permissionCheckerLocator
            ->expects($this->once())
            ->method('has')
            ->with($permission::class)
            ->willReturn(false);

        $this->expectException(PermissionCheckerNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'You need to implement (and register) the permission checker (%s) for permission "%s".',
                PermissionCheckerInterface::class,
                $permission::class,
            ),
        );

        $this->validator->validate($permission, $constraint);
    }

    #[Test]
    public function it_calls_checker_with_permission_and_context_when_checker_is_registered(): void
    {
        $constraint = new PermissionConstraint();
        $permission = $this->createMock(PermissionInterface::class);
        $checker = $this->createMock(PermissionCheckerInterface::class);

        $this->permissionCheckerLocator
            ->expects($this->once())
            ->method('has')
            ->with($permission::class)
            ->willReturn(true);

        $this->permissionCheckerLocator
            ->expects($this->once())
            ->method('get')
            ->with($permission::class)
            ->willReturn($checker);

        $checker
            ->expects($this->once())
            ->method('check')
            ->with($permission, $this->executionContext);

        $this->validator->validate($permission, $constraint);
    }
}
