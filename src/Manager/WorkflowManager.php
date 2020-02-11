<?php


namespace whatwedo\WorkflowBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\WorkflowBundle\DTO\WorkflowMetadataStore;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\Entity\WorkflowLog;
use whatwedo\WorkflowBundle\EventHandler\EventHandlerAbstract;
use whatwedo\WorkflowBundle\EventHandler\WorkflowEventHandlerInterface;
use whatwedo\WorkflowBundle\Repository\EventDefinitionRepository;
use whatwedo\WorkflowBundle\Repository\TransitionRepository;
use whatwedo\WorkflowBundle\Repository\WorkflowLogRepository;
use whatwedo\WorkflowBundle\Repository\WorkflowRepository;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;

class WorkflowManager
{
    /** @var \Doctrine\Persistence\ManagerRegistry */
    private $doctrine;

    /** @var array|WorkflowEventHandlerInterface */
    protected $workflowEventHandler = [];
    
    /**
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine
     * @required
     */
    public function setDoctrine(\Doctrine\Persistence\ManagerRegistry $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    public function addWorkflowEventHandler(WorkflowEventHandlerInterface $workflowEventHandler)
    {
        $this->workflowEventHandler[get_class($workflowEventHandler)] = $workflowEventHandler;
    }

    public function getWorkflowsForEntity(object $subject) {
        /** @var WorkflowRepository $workflowRepo */
        $workflowRepo = $this->doctrine->getRepository(Workflow::class);

        $allWorkflows = $workflowRepo->findAll();

        $class = get_class($subject);
        $workflows = [];

        foreach ($allWorkflows as $workflow) {
            if (in_array($class, $workflow->getSupports())) {
                $workflows[] = $workflow;
            }
        }

        return $workflows;
    }

    public function getWorkflow(\Symfony\Component\Workflow\Workflow $workflow): Workflow
    {
        /** @var WorkflowRepository $workflowRepo */
        $workflowRepo = $this->doctrine->getRepository(Workflow::class);

        $wwdWorkflow = $workflowRepo->findOneByName($workflow->getName());

        return $wwdWorkflow;
    }

    public function getPlace(string $place): Place
    {
        /** @var WorkflowRepository $workflowRepo */
        $placeRepo = $this->doctrine->getRepository(Place::class);

        $wwdPlace = $placeRepo->findOneByName($place);

        return $wwdPlace;
    }

    public function getDefinition(Workflow $workflow)
    {
        $definitionBuilder = new DefinitionBuilder();

        foreach ($workflow->getPlaces() as $place) {
            $definitionBuilder->addPlace($place->getName());
        }
        foreach ($workflow->getTransitions() as $transition) {
            $tos = [];
            foreach ($transition->getTos() as $to) {
                $tos[] = $to->getName();
            }
            $froms = [];
            foreach ($transition->getFroms() as $from) {
                $froms[] = $from->getName();
            }
            $definitionBuilder->addTransition(new Transition($transition->getName(), $froms, $tos));
        }

        $definitionBuilder->setMetadataStore(new WorkflowMetadataStore($workflow));
        if ($workflow->getInitialPlace()) {
            $definitionBuilder->setInitialPlaces($workflow->getInitialPlace()->getName());
        }

        $definition = $definitionBuilder->build();

        return $definition;
    }


    /**
     * @return Workflow[]
     */
    public function getAllWorkflows()
    {
        /** @var WorkflowRepository $repository */
        $repository = $this->doctrine->getRepository(Workflow::class);
        return $repository->findAll();
    }

    /**
     * @param string $name
     * @return null|\whatwedo\WorkflowBundle\Entity\Transition
     */
    public function getTransition(string $name) : ?\whatwedo\WorkflowBundle\Entity\Transition
    {
        /** @var TransitionRepository $repository */
        $repository = $this->doctrine->getRepository(\whatwedo\WorkflowBundle\Entity\Transition::class);
        return $repository->findOneBy(['name' => $name]);
    }

    /**
     * @return EventDefinition[]|null
     */
    public function getCheckPlaceDefnitions()
    {
        /** @var EventDefinitionRepository $repository */
        $repository = $this->doctrine->getRepository(EventDefinition::class);
        return $repository->findBy(['eventName' => EventDefinition::CHECK]);
    }


    /**
     * @return object[]
     */
    public function getEntitiesInPlace(Place $place, ?string $entityClass = null): array
    {
        $result = [];
        /** @var Workflowable $dummyEntity */
        if (!$entityClass) {
            $entityClass = $place->getWorkflow()->getSupports()[0];
        }

        $dummyEntity = new $entityClass;
        if ($queryResults = $this->doctrine->getRepository($entityClass)->findBy([$dummyEntity->getCurrentPlaceField() => $place->getName()])) {
            return $queryResults;
        }

        return $result;

    }

    public function getEventHandler(EventDefinition $eventDefinition, string $eventName = null): ?WorkflowEventHandlerInterface
    {
        $result = null;
        if (($eventName == null || $eventDefinition->getEventName() === $eventName) && !empty($eventDefinition->getEventHandler())) {
            $eventHandlerClass = $eventDefinition->getEventHandler();
            if (isset($this->workflowEventHandler[$eventHandlerClass])) {
                return $this->workflowEventHandler[$eventHandlerClass];
            }
        }
        return $result;
    }


    public function getLastEventLogForEntity($checkPlaceEntity, EventDefinition $eventDefintion) : ? WorkflowLog
    {
        /** @var WorkflowLogRepository $repository */
        $repository = $this->doctrine->getRepository(WorkflowLog::class);
        $log = $repository->findOneBy(['eventDefinition' => $eventDefintion]);
        return $log;
    }


    public function isValid(EventDefinition $eventDefintion) {
        if ($eventDefintion->getEventHandler()) {
            $eventHandler = $this->getEventHandler($eventDefintion);

            return $eventDefintion->getExpression() && $eventHandler->validateExpression($eventDefintion) &&
                $eventDefintion->getTemplate() && $eventHandler->validateTemplate($eventDefintion);
        }

        return false;
    }
}