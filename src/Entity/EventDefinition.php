<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\Repository\EventDefinitionRepository")
 * @ORM\Table(name="whatwedo_workflow_event_definition")
 */
class EventDefinition
{
    const GUARD         = 'guard';
    const TRANSITION    = 'transition';
    const COMPLETED     = 'completed';
    const ANNOUNCE      = 'announce';

    const LEAVE     = 'leave';
    const ENTER     = 'enter';
    const ENTERED   = 'entered';
    const CHECK     = 'check';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|Transition
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", inversedBy="eventDefinitions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $transition;

    /**
     * @var null|Place
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Place", inversedBy="eventDefinitions")
     * @ORM\JoinColumn(nullable=true)
     */
    private $place;

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $eventName;

    /**
     * @var null|string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $eventHandler;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     */
    private $sortorder = 0;

    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private $expression;

    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    /**
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private $template;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $applyOnce = false;


    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Transition|null
     */
    public function getTransition(): ?Transition
    {
        return $this->transition;
    }

    /**
     * @param Transition|null $transition
     */
    public function setTransition(?Transition $transition): void
    {
        $this->transition = $transition;
    }

    /**
     * @return Place|null
     */
    public function getPlace(): ?Place
    {
        return $this->place;
    }

    /**
     * @param Place|null $place
     */
    public function setPlace(?Place $place): void
    {
        $this->place = $place;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    /**
     * @param string|null $eventName
     */
    public function setEventName(?string $eventName): void
    {
        $this->eventName = $eventName;
    }

    /**
     * @return string|null
     */
    public function getEventHandler(): ?string
    {
        return $this->eventHandler;
    }

    /**
     * @param string|null $eventHandler
     */
    public function setEventHandler(?string $eventHandler): void
    {
        $this->eventHandler = $eventHandler;
    }

    /**
     * @return int
     */
    public function getSortorder(): int
    {
        return $this->sortorder;
    }

    /**
     * @param int $sortorder
     */
    public function setSortorder(int $sortorder): void
    {
        $this->sortorder = $sortorder;
    }

    /**
     * @return string|null
     */
    public function getExpression(): ?string
    {
        return $this->expression;
    }

    /**
     * @param string|null $expression
     */
    public function setExpression(?string $expression): void
    {
        $this->expression = $expression;
    }

    /**
     * @return string|null
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string|null $template
     */
    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return bool
     */
    public function isApplyOnce(): bool
    {
        return $this->applyOnce;
    }

    /**
     * @param bool $applyOnce
     */
    public function setApplyOnce(bool $applyOnce): void
    {
        $this->applyOnce = $applyOnce;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
