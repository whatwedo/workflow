<?php


namespace whatwedo\WorkflowBundle\DTO;


use whatwedo\WorkflowBundle\Entity\Workflow;
use Symfony\Component\Workflow\Metadata\GetMetadataTrait;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;
use Symfony\Component\Workflow\Transition;

class WorkflowMetadataStore implements MetadataStoreInterface
{

    use GetMetadataTrait;

    /**
     * @var Workflow
     */
    protected $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function getWorkflowMetadata(): array
    {
        return ['data' => $this->workflow];
    }

    public function getPlaceMetadata(string $place): array
    {
        $result = null;

        foreach ($this->workflow->getPlaces() as $place)  {
            if ($place->getName() === $place) {
                $result = $place;
                break;
            }
        }
        return ['data' => $result];
    }

    public function getTransitionMetadata(Transition $transition): array
    {
        $result = null;

        foreach ($this->workflow->getTransitions() as $item)  {
            if ($item->getName() === $transition->getName()) {
                $result = $item;
                break;
            }
        }
        return ['data' => $result];
    }


}