<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\Repository\PlaceRepository")
 * @ORM\Table(name="whatwedo_workflow_place")
 */
class Place
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Workflow", inversedBy="places")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer", name="placeLimit", nullable=true)
     */
    private $limit = 0;


    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", mappedBy="froms")
     */
    private $fromTransitions;

    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", mappedBy="tos")
     */
    private $toTransitions;

    /**
     * @ORM\OneToMany(targetEntity="whatwedo\WorkflowBundle\Entity\PlaceEventDefinition", mappedBy="place")
     */
    private $eventDefinitions;


    public function __construct(\whatwedo\WorkflowBundle\Entity\Workflow $workflow)
    {
        $this->workflow = $workflow;
        $this->fromTransitions = new ArrayCollection();
        $this->toTransitions = new ArrayCollection();
        $this->eventDefinitions = new ArrayCollection();

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getWorkflow(): ?Workflow
    {
        return $this->workflow;
    }

    public function setWorkflow(?Workflow $workflow): self
    {
        $this->workflow = $workflow;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Transition[]
     */
    public function getFromTransitions(): Collection
    {
        return $this->fromTransitions;
    }

    public function addFromTransition(Transition $fromTransition): self
    {
        if (!$this->fromTransitions->contains($fromTransition)) {
            $this->fromTransitions[] = $fromTransition;
            $fromTransition->addFrom($this);
        }

        return $this;
    }

    public function removeFromTransition(Transition $fromTransition): self
    {
        if ($this->fromTransitions->contains($fromTransition)) {
            $this->fromTransitions->removeElement($fromTransition);
            $fromTransition->removeFrom($this);
        }

        return $this;
    }

    /**
     * @return Collection|Transition[]
     */
    public function getToTransitions(): Collection
    {
        return $this->toTransitions;
    }

    public function addToTransition(Transition $toTransition): self
    {
        if (!$this->toTransitions->contains($toTransition)) {
            $this->toTransitions[] = $toTransition;
            $toTransition->addTo($this);
        }

        return $this;
    }

    public function removeToTransition(Transition $toTransition): self
    {
        if ($this->toTransitions->contains($toTransition)) {
            $this->toTransitions->removeElement($toTransition);
            $toTransition->removeTo($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEventDefinitions(): Collection
    {
        return $this->eventDefinitions;
    }



    public function __toString()
    {
        return $this->name;
    }
}
