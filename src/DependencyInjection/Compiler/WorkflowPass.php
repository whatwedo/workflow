<?php

namespace whatwedo\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Bridge\Doctrine\RegistryInterface;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\Form\EventSubscriberTypes;
use whatwedo\WorkflowBundle\Form\WorkflowSupportedTypes;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class WorkflowPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $eventSubscriberTypes = $container->getDefinition(EventSubscriberTypes::class);
        $taggedServices = $container->findTaggedServiceIds('workflow.event_handler');

        foreach ($taggedServices as $id => $tags) {
            $eventSubscriberTypes->addMethodCall('addWorkflowSubscriber', [
                new Reference($id)
            ]);
        }
    }
}