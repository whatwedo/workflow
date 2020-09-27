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
     * @var boolean
     * @ORM\Column(type="boolean",)
     */
    private $hideInMenu = false;

    /**
     * @var int
     * @ORM\Column(type="smallint")
     */
    private $sort = 0;

    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", mappedBy="froms")
     */
    private $fromTransitions;

    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", mappedBy="tos")
     */
    private $toTransitions;

    /**
     * @ORM\OneToMany(targetEntity="whatwedo\WorkflowBundle\Entity\EventDefinition", mappedBy="place")
     * @ORM\OrderBy({"sortorder" = "ASC"})
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
     * @return bool
     */
    public function isHideInMenu(): bool
    {
        return $this->hideInMenu;
    }

    /**
     * @param bool $hideInMenu
     */
    public function setHideInMenu(bool $hideInMenu): void
    {
        $this->hideInMenu = $hideInMenu;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
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
    public function getEventDefinitions($eventName = null): Collection
    {
        if (!$eventName) {
            return $this->eventDefinitions;
        } else {
            return $this->eventDefinitions->filter(
                function (EventDefinition $data) use ($eventName) {
                    return $data->getEventName() == $eventName;
                }
            );
        }
    }



    public function __toString()
    {
        return $this->name;
    }
}
