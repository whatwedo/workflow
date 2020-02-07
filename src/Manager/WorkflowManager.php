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

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     * @required
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine
     * @required
     */
    public function setDoctrine(\Doctrine\Persistence\ManagerRegistry $doctrine): void
    {
        $this->doctirine = $doctrine;
    }

    public function getWorkflowsForEntity(object $subject) {
        /** @var WorkflowRepository $workflowRepo */
        $workflowRepo = $this->doctirine->getRepository(Workflow::class);

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
        $placeRepo = $this->doctirine->getRepository(Place::class);

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
        $repository = $this->doctirine->getRepository(Workflow::class);
        return $repository->findAll();
    }

    /**
     * @param string $name
     * @return null|\whatwedo\WorkflowBundle\Entity\Transition
     */
    public function getTransition(string $name) : ?\whatwedo\WorkflowBundle\Entity\Transition
    {
        /** @var TransitionRepository $repository */
        $repository = $this->doctirine->getRepository(\whatwedo\WorkflowBundle\Entity\Transition::class);
        return $repository->findOneBy(['name' => $name]);
    }

    /**
     * @return EventDefinition[]|null
     */
    public function getCheckPlaceDefnitions()
    {
        /** @var EventDefinitionRepository $repository */
        $repository = $this->doctirine->getRepository(EventDefinition::class);
        return $repository->findBy(['eventName' => EventDefinition::CHECK]);
    }


    /**
     * @param string $entityClass
     * @param string $place
     * @return object[]
     */
    public function getEntitiesInPlace(string $entityClass, string $place)
    {
        /** @var Workflowable $dummyEntity */
        $dummyEntity = new $entityClass;
        return $this->doctirine->getRepository($entityClass)->findBy([$dummyEntity->getCurrentPlaceField() => $place]);
    }

    public function getEventHandler(EventDefinition $eventDefinition, string $eventName = null): ?EventHandlerAbstract
    {
        $result = null;
        if (($eventName == null || $eventDefinition->getEventName() === $eventName) && !empty($eventDefinition->getEventHandler())) {
            $eventHandlerClass = $eventDefinition->getEventHandler();
            /** @var EventHandlerAbstract $eventHandler */
            $eventHandler = $this->container->get($eventHandlerClass);

            $result = $eventHandler;
        }
        return $result;
    }


    public function getLastEventLogForEntity($checkPlaceEntity, EventDefinition $eventDefintion) : ? WorkflowLog
    {
        /** @var WorkflowLogRepository $repository */
        $repository = $this->doctirine->getRepository(WorkflowLog::class);
        $log = $repository->findOneBy(['eventDefinition' => $eventDefintion]);
        return $log;
    }
}