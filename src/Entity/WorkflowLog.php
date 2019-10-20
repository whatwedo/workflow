<?php


namespace whatwedo\WorkflowBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\VarDumper\Tests\Cloner\DataTest;

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
     */
    private $transition;

    /**
     * @var Transition
     * @ORM\ManyToOne(targetEntity="whatwedo\WorkflowBundle\Entity\TransitionEventDefinition")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transitionEventDefinition;

    /**
     *
     * @var null|boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $success;

    /**
     *
     * @var null|string
     * @ORM\Column(type="text", nullable=true)
     */
    private $log;

    public function __construct(Workflowable $subject, Transition $transition)
    {
        $this->subjectClass = get_class($subject);
        $this->subjectId = $subject->getId();
        $this->transition = $transition;
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
     * @return Transition
     */
    public function getTransitionEventDefinition(): Transition
    {
        return $this->transitionEventDefinition;
    }

    /**
     * @param Transition $transitionEventDefinition
     */
    public function setTransitionEventDefinition(TransitionEventDefinition $transitionEventDefinition): void
    {
        $this->transitionEventDefinition = $transitionEventDefinition;
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

}