<?php
namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="whatwedo\WorkflowBundle\Repository\WorkflowLogRepository")
 * @ORM\Table(name="whatwedo_workflow_workflow_log")
 */
class WorkflowLog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $subjectClass;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $subjectId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $date;


    /**
     * @var Transition
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Transition")
     * @ORM\JoinColumn(nullable=true)
     */
    private $transition;


    /**
     * @var Place
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\Place")
     * @ORM\JoinColumn(nullable=true)
     */
    private $place;

    /**
     * @var EventDefinition
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\EventDefinition")
     * @ORM\JoinColumn(nullable=true)
     */
    private $eventDefinition;

    /**
     *
     * @var null|boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $success;

    /**
     *
     * @var null|string
     * @ORM\Column(type="object", nullable=true)
     */
    private $data;


    /**
     *
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;

    public function __construct(Workflowable $subject)
    {
        $this->subjectClass = get_class($subject);
        $this->subjectId = $subject->getId();
        $this->date = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubjectClass(): string
    {
        return $this->subjectClass;
    }

    /**
     * @return int
     */
    public function getSubjectId(): int
    {
        return $this->subjectId;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return Transition
     */
    public function getTransition(): Transition
    {
        return $this->transition;
    }

    /**
     * @param Transition $transition
     */
    public function setTransition(Transition $transition): void
    {
        $this->transition = $transition;
    }

    /**
     * @return bool|null
     */
    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * @param bool|null $success
     */
    public function setSuccess(?bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return string|null
     */
    public function getLog(): ?string
    {
        return $this->log;
    }

    /**
     * @param string|null $log
     */
    public function setLog(?string $log): void
    {
        $this->log = $log;
    }

    /**
     * @return Place
     */
    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * @param Place $place
     */
    public function setPlace(Place $place): void
    {
        $this->place = $place;
    }

    /**
     * @return EventDefinition
     */
    public function getEventDefinition(): EventDefinition
    {
        return $this->eventDefinition;
    }

    /**
     * @param EventDefinition $eventDefinition
     */
    public function setEventDefinition(EventDefinition $eventDefinition): void
    {
        $this->eventDefinition = $eventDefinition;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
