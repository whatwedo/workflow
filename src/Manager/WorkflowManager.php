<?php


namespace whatwedo\WorkflowBundle\Manager;

use whatwedo\WorkflowBundle\DTO\WorkflowMetadataStore;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Repository\WorkflowRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Transition;

class WorkflowManager
{
    /** @var RegistryInterface */
    private $doctirine;

    /**
     * @param RegistryInterface $doctirine
     * @required
     */
    public function setDoctirine(RegistryInterface $doctirine): void
    {
        $this->doctirine = $doctirine;
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

        $definition = $definitionBuilder->build();

        return $definition;
    }


    /**
     * @return Workflow[]
     */
    public function getAllWorkflows()
    {
        return $this->doctirine->getRepository(Workflow::class)->findAll();
    }

    /**
     * @param string $name
     * @return null|Transition
     */
    public function getTransition(string $name) : Transition
    {
        return $this->doctirine->getRepository(Workflow::class)->findOneBy(['name' => $name]);
    }
}