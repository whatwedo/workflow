<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\RepositoryWorkflowRepository")
 * @ORM\Table(name="whatwedo_workflow_workflow")
 */
class Workflow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $type;

  
    /**
     * @ORM\Column(type="json")
     */
    private $supports = [];

    /**
     * @ORM\Column(type="json")
     */
    private $markingStore = [];

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $singleState = true;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $property;

    /**
     * @ORM\OneToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Place", mappedBy="workflow")
     */
    private $places;

    /**
     * @ORM\OneToMany(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", mappedBy="workflow")
     */
    private $transitions;

    public function __construct()
    {
        $this->places = new ArrayCollection();
        $this->transitions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }


    public function getSupports(): ?array
    {
        return $this->supports;
    }

    public function setSupports(array $supports): self
    {
        $this->supports = $supports;

        return $this;
    }

    public function getMarkingStore(): ?array
    {
        return $this->markingStore;
    }

    public function setMarkingStore(array $markingStore): self
    {
        $this->markingStore = $markingStore;

        return $this;
    }

    /**
     * @return Collection|Place[]
     */
    public function getPlaces(): Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setWorkflow($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getWorkflow() === $this) {
                $place->setWorkflow(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transition[]
     */
    public function getTransitions(): Collection
    {
        return $this->transitions;
    }

    public function addTransition(Transition $transition): self
    {
        if (!$this->transitions->contains($transition)) {
            $this->transitions[] = $transition;
            $transition->setWorkflow($this);
        }

        return $this;
    }

    public function removeTransition(Transition $transition): self
    {
        if ($this->transitions->contains($transition)) {
            $this->transitions->removeElement($transition);
            // set the owning side to null (unless already changed)
            if ($transition->getWorkflow() === $this) {
                $transition->setWorkflow(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSingleState(): bool
    {
        return $this->singleState;
    }

    /**
     * @param bool $singleState
     */
    public function setSingleState(bool $singleState): void
    {
        $this->singleState = $singleState;
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @param string $property
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }


}
