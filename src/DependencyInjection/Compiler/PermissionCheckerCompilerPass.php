<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\DependencyInjection\Compiler;

use Sybio\PermissionBundle\PermissionCheckerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function is_subclass_of;

class PermissionCheckerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sybio_permission.checker_locator')) {
            return;
        }

        /** @see PermissionCheckerInterface */
        $locatorDefinition = $container->getDefinition('sybio_permission.checker_locator');

        // Get all services tagged as permission checkers
        $taggedServices = $container->findTaggedServiceIds('sybio_permission.checker');
        $callables = [];

        foreach ($taggedServices as $serviceId => $tags) {
            $serviceDefinition = $container->getDefinition($serviceId);
            $serviceClass = $serviceDefinition->getClass();

            // Call static method permissionClass()
            if ($serviceClass !== null
                && is_subclass_of($serviceClass, PermissionCheckerInterface::class, true)
            ) {
                $permissionClass = $serviceClass::permissionClass();
                $callables[$permissionClass] = new Reference($serviceId);
            }
        }

        $locatorDefinition->setArgument(0, $callables);
    }
}
