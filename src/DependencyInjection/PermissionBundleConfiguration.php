<?php

declare(strict_types=1);

namespace Sybio\PermissionBundle\DependencyInjection;

use Sybio\PermissionBundle\Decision\PermissionDecisionFactory;
use Sybio\PermissionBundle\Decision\PermissionDecisionFactoryInterface;
use Sybio\PermissionBundle\PermissionCheckerInterface;
use Sybio\PermissionBundle\Security\PermissionVoter;
use Sybio\PermissionBundle\Twig\HasPermissionTwigFunction;
use Sybio\PermissionBundle\Twig\PermissionTwigFunction;
use Sybio\PermissionBundle\Validation\PermissionConstraintValidator;
use Sybio\PermissionBundle\Validation\PermissionValidator;
use Sybio\PermissionBundle\Validation\PermissionValidatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;
use function is_string;

class PermissionBundleConfiguration extends Extension implements ConfigurationInterface
{
    public function load(
        array $configs,
        ContainerBuilder $container,
    ): void {
        $config = $this->processConfiguration($this, $configs);

        $securityAttribute = $config['security_attribute'];
        if (!is_string($securityAttribute)) {
            $securityAttribute = 'PERMISSION';
        }

        $container->setParameter('sybio_permission.security_attribute', $securityAttribute);

        $container->registerForAutoconfiguration(PermissionCheckerInterface::class)
            ->addTag('sybio_permission.checker');

        $locatorDefinition = new Definition(ServiceLocator::class);
        $locatorDefinition->setFactory([ServiceLocator::class, 'fromCallables']);
        $locatorDefinition->addTag('container.service_locator');

        $container->setDefinition('sybio_permission.checker_locator', $locatorDefinition);

        /** @see PermissionConstraintValidator */
        $container->setDefinition(
            'sybio_permission.constraint_validator',
            (new Definition(PermissionConstraintValidator::class))
                ->setArgument(0, new Reference('sybio_permission.checker_locator'))
                ->setPublic(true)
                ->addTag('validator.constraint_validator'),
        );

        /** @see PermissionValidator */
        $container->setDefinition(
            'sybio_permission.validator',
            (new Definition(PermissionValidator::class))
                ->setArgument(0, new Reference('validator'))
                ->setPublic(true),
        );

        /** @see PermissionVoter */
        $container->setDefinition(
            'sybio_permission.voter',
            (new Definition(PermissionVoter::class))
                ->setArgument(0, new Reference('sybio_permission.validator'))
                ->setArgument(1, $container->getParameter('sybio_permission.security_attribute'))
                ->setPublic(true)
                ->addTag('security.voter'),
        );

        /** @see PermissionDecisionFactory */
        $container->setDefinition(
            'sybio_permission.result_factory',
            (new Definition(PermissionDecisionFactory::class))
                ->setPublic(false),
        );

        /** @see HasPermissionTwigFunction */
        $container->setDefinition(
            'sybio_permission.twig.has_permission',
            (new Definition(HasPermissionTwigFunction::class))
                ->setArgument(0, new Reference('security.authorization_checker'))
                ->setArgument(1, $container->getParameter('sybio_permission.security_attribute'))
                ->addTag('twig.extension')
                ->setPublic(false),
        );

        /** @see PermissionTwigFunction */
        $container->setDefinition(
            'sybio_permission.twig.permission',
            (new Definition(PermissionTwigFunction::class))
                ->setArgument(0, new Reference('security.authorization_checker'))
                ->setArgument(1, new Reference('sybio_permission.validator'))
                ->setArgument(2, new Reference('sybio_permission.result_factory'))
                ->addTag('twig.extension')
                ->setPublic(false),
        );

        $container->setAlias(PermissionConstraintValidator::class, 'sybio_permission.constraint_validator');
        $container->setAlias(PermissionValidatorInterface::class, 'sybio_permission.validator');
        $container->setAlias(PermissionValidator::class, 'sybio_permission.validator');
        $container->setAlias(PermissionVoter::class, 'sybio_permission.voter');
        $container->setAlias(PermissionDecisionFactoryInterface::class, 'sybio_permission.result_factory');
    }

    public function getAlias(): string
    {
        return 'sybio_permission';
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sybio_permission');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->scalarNode('security_attribute')
            ->defaultValue('PERMISSION')
            ->end()
        ;

        return $treeBuilder;
    }
}
