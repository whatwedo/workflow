<?php

namespace whatwedo\WorkflowBundle\Service;


use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\EventSubscriber\WorkflowSubscriber;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class WorkflowService extends Registry
{
    /**
     * @var WorkflowManager
     */
    private $manager;

    /** @var WorkflowSubscriber */
    private $workflowListener;


    public function __construct(WorkflowManager $manager, WorkflowSubscriber $workflowListener)
    {
//        throw new \Exception('peng');
        $this->manager = $manager;
        $this->workflowListener = $workflowListener;

        /** @var Workflow[] $workflows */
        $workflows = $this->manager->getAllWorkflows();


        foreach ($workflows as $workflow) {
            $definition = $this->manager->getDefinition($workflow);
            $dispatcher = new EventDispatcher();
            foreach (WorkflowSubscriber::getSubscribedEvents() as $eventName => $event) {
                $dispatcher->addListener($eventName, array($this->workflowListener, $event));
            }

            $wf = new \Symfony\Component\Workflow\Workflow(
                $definition,
                new MethodMarkingStore($workflow->isSingleState(), 'currentPlace'),
                $dispatcher,
                $workflow->getName()
            );

            $this->addWorkflow($wf, new InstanceOfSupportStrategy($workflow->getSupports()[0]));
        }
    }
}
