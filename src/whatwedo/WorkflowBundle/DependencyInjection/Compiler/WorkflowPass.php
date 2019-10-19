<?php


namespace whatwedo\WorkflowBundle\DependencyInjection\Compiler;


use whatwedo\WorkflowBundle\Form\TransitionEventDefinitionType;
use whatwedo\WorkflowBundle\Form\WorkflowEventSubscriberTypes;
use whatwedo\WorkflowBundle\EventHandler\IWorkflowSubscriber;
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
        $definition = $container->getDefinition(WorkflowEventSubscriberTypes::class);
        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('workflow.event_handler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addWorkflowSubscriber', [
                new Reference($id)
            ]);
        }
    }
}