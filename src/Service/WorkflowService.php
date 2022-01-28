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

    private $workflows = [];


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
        } catch (\Doctrine\DBAL\Exception\InvalidFieldNameException $ex) {
            echo   "\e[0;33m"  . PHP_EOL;
            echo   '    Update Workflow Tables    ';
            echo "\e[0m" . PHP_EOL . PHP_EOL;
        } catch (\Doctrine\DBAL\Exception\ConnectionException $ex) {
            echo   "\e[0;33m"  . PHP_EOL;
            echo   '    Workflow DB does not exists    ';
            echo "\e[0m" . PHP_EOL . PHP_EOL;
        }
    }


    public function addWorkflow(WorkflowInterface $workflow, WorkflowSupportStrategyInterface $supportStrategy)
    {
        $this->workflows[] = [$workflow, $supportStrategy];
    }

    public function has(object $subject, string $workflowName = null): bool
    {
        foreach ($this->workflows as [$workflow, $supportStrategy]) {
            if ($this->supports($workflow, $supportStrategy, $subject, $workflowName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Workflow
     */
    public function get(object $subject, string $workflowName = null): Workflow
    {
        $matched = [];

        foreach ($this->workflows as [$workflow, $supportStrategy]) {
            if ($this->supports($workflow, $supportStrategy, $subject, $workflowName)) {
                $matched[] = $workflow;
            }
        }

        if (!$matched) {
            throw new InvalidArgumentException(sprintf('Unable to find a workflow for class "%s".', get_debug_type($subject)));
        }

        if (2 <= \count($matched)) {
            $names = array_map(static function (WorkflowInterface $workflow): string {
                return $workflow->getName();
            }, $matched);

            throw new InvalidArgumentException(sprintf('Too many workflows (%s) match this subject (%s); set a different name on each and use the second (name) argument of this method.', implode(', ', $names), get_debug_type($subject)));
        }

        return $matched[0];
    }

    /**
     * @return Workflow[]
     */
    public function all(object $subject): array
    {
        $matched = [];
        foreach ($this->workflows as [$workflow, $supportStrategy]) {
            if ($supportStrategy->supports($workflow, $subject)) {
                $matched[] = $workflow;
            }
        }

        return $matched;
    }

    private function supports(WorkflowInterface $workflow, WorkflowSupportStrategyInterface $supportStrategy, object $subject, ?string $workflowName): bool
    {
        if (null !== $workflowName && $workflowName !== $workflow->getName()) {
            return false;
        }

        return $supportStrategy->supports($workflow, $subject);
    }
}
