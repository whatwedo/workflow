<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\RepositoryTransitionEventDefinitionRepository")
 */
class TransitionEventDefinition
{
    const GUARD         = 'guard';
    const TRANSITION    = 'transition';
    const COMPLETED     = 'completed';
    const ANNOUNCE      = 'announce';
    const ENTER         = 'enter';
    const ENTERED       = 'entered';
    const LEAVE         = 'leave';


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|Transition
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Transition", inversedBy="eventDefinitions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transition;

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
    private $eventSubscriber;

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
    private $template;


    public function __construct(Transition $transition)
    {
        $this->transition = $transition;
    }

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
    public function getEventSubscriber(): ?string
    {
        return $this->eventSubscriber;
    }

    /**
     * @param string|null $eventSubscriber
     */
    public function setEventSubscriber(?string $eventSubscriber): void
    {
        $this->eventSubscriber = $eventSubscriber;
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




}
