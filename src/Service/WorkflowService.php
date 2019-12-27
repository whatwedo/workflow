<?php

namespace whatwedo\WorkflowBundle\Service;


use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\EventSubscriber\WorkflowSubscriber;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;
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

        $this->manager = $manager;
        $this->workflowListener = $workflowListener;

        try {

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
        } catch (\Doctrine\DBAL\Exception\TableNotFoundException $ex) {
            echo   "\e[0;33m"  . PHP_EOL;
            echo   '    Create Workflow Tables    ';
            echo "\e[0m" . PHP_EOL . PHP_EOL;
        }
    }
}
