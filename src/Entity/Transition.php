<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\Repository\TransitionRepository")
 * @ORM\Table(name="whatwedo_workflow_transition")
 */
class Transition
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Workflow", inversedBy="transitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $workflow;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Place", inversedBy="fromTransitions")
     * @ORM\JoinTable(name="transition_from_place")
     */
    private $froms;

    /**
     * @ORM\ManyToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Place", inversedBy="toTransitions")
     * @ORM\JoinTable(name="transition_to_place")
     */
    private $tos;

    /**
     * @ORM\OneToMany(targetEntity="whatwedo\WorkflowBundle\Entity\EventDefinition", mappedBy="transition")
     * @ORM\OrderBy({"sortorder" = "ASC"})
     */
    private $eventDefinitions;

    public function __construct(\whatwedo\WorkflowBundle\Entity\Workflow $workflow)
    {
        $this->workflow = $workflow;
        $this->froms = new ArrayCollection();
        $this->tos = new ArrayCollection();
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
     * @return Collection|Place[]
     */
    public function getFroms(): Collection
    {
        return $this->froms;
    }

    public function addFrom(Place $from): self
    {
        if (!$this->froms->contains($from)) {
            $this->froms[] = $from;
        }

        return $this;
    }

    public function removeFrom(Place $from): self
    {
        if ($this->froms->contains($from)) {
            $this->froms->removeElement($from);
        }

        return $this;
    }

    /**
     * @return Collection|Place[]
     */
    public function getTos(): Collection
    {
        return $this->tos;
    }

    public function addTo(Place $to): self
    {
        if (!$this->tos->contains($to)) {
            $this->tos[] = $to;
        }

        return $this;
    }

    public function removeTo(Place $to): self
    {
        if ($this->tos->contains($to)) {
            $this->tos->removeElement($to);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getEventDefinitions():Collection
    {
        return $this->eventDefinitions;
    }
}
