<?php

namespace whatwedo\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use whatwedo\WorkflowBundle\EventHandler\EventHandlerInterface;
use whatwedo\WorkflowBundle\EventHandler\TransitionGuardHandlerInterface;
use whatwedo\WorkflowBundle\EventHandler\WorkflowEventHandlerInterface;


class WorkflowBundleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(EventHandlerInterface::class)
            ->addTag('workflow.event_handler')
            ->setPublic(true);
        $container->registerForAutoconfiguration(TransitionGuardHandlerInterface::class)
            ->addTag('workflow.transition_guard')
            ->setPublic(true);
    }
}