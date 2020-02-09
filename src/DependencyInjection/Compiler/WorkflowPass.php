<?php

namespace whatwedo\WorkflowBundle\DependencyInjection\Compiler;

use Doctrine\Persistence\ManagerRegistry;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\Form\EventHandlerTypes;
use whatwedo\WorkflowBundle\Form\WorkflowSupportedTypes;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class WorkflowPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $eventHandlerTypesDefinition = $container->getDefinition(EventHandlerTypes::class);
        $taggedServices = $container->findTaggedServiceIds('workflow.event_handler');
        $workflowManagerDefintion = $container->getDefinition(WorkflowManager::class);

        foreach ($taggedServices as $id => $tags) {

            $reference = new Reference($id);

            $eventHandlerTypesDefinition->addMethodCall('addWorkflowSubscriber', [
                $reference
            ]);
            $workflowManagerDefintion->addMethodCall('addWorkflowEventHandler', [
                $reference
            ]);
        }
    }
}