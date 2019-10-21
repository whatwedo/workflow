<?php

namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\Repository\PlaceEventDefinitionRepository")
 * @ORM\Table(name="whatwedo_workflow_place_event_definiton")
 */
class PlaceEventDefinition
{

    const LEAVE     = 'leave';
    const ENTER     = 'enter';
    const ENTERED   = 'entered';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|Place
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Place", inversedBy="eventDefinitions")
     * @ORM\JoinColumn(nullable=false)
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


    public function __construct(Place $place)
    {
        $this->place = $place;
    }

    public function getId(): ?int
    {
        return $this->id;
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
