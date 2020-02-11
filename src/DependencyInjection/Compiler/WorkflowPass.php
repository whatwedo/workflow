<?php

namespace whatwedo\WorkflowBundle\DependencyInjection\Compiler;

use Doctrine\Persistence\ManagerRegistry;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\Form\EventHandlerTypes;
use whatwedo\WorkflowBundle\Form\TransitionGuardHandlerTypes;
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
        $workflowManagerDefintion = $container->getDefinition(WorkflowManager::class);

        $taggedServices = $container->findTaggedServiceIds('workflow.event_handler');
        foreach ($taggedServices as $id => $tags) {

            $reference = new Reference($id);

            $eventHandlerTypesDefinition->addMethodCall('addWorkflowHandler', [
                $reference
            ]);
            $workflowManagerDefintion->addMethodCall('addWorkflowEventHandler', [
                $reference
            ]);
        }

        $guardHandlerTypesDefinition = $container->getDefinition(TransitionGuardHandlerTypes::class);
        $taggedServices = $container->findTaggedServiceIds('workflow.transition_guard');
        foreach ($taggedServices as $id => $tags) {

            $reference = new Reference($id);

            $guardHandlerTypesDefinition->addMethodCall('addGuardHandler', [
                $reference
            ]);
            $workflowManagerDefintion->addMethodCall('addWorkflowEventHandler', [
                $reference
            ]);

        }


    }
}