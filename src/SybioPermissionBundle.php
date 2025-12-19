<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle;

use Sybio\PermissionBundle\DependencyInjection\Compiler\PermissionCheckerCompilerPass;
use Sybio\PermissionBundle\DependencyInjection\PermissionBundleConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SybioPermissionBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PermissionCheckerCompilerPass());
    }

    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new PermissionBundleConfiguration();
    }
}
