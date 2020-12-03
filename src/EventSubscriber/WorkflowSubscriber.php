<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 02.01.19
 * Time: 17:01
 */

namespace whatwedo\WorkflowBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Entity\WorkflowLog;
use whatwedo\WorkflowBundle\EventHandler\EventHandlerAbstract;
use whatwedo\WorkflowBundle\EventHandler\TransitionsEventHandlerAbstract;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Symfony\Component\Workflow\Event\AnnounceEvent;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Event\EnteredEvent;
use Symfony\Component\Workflow\Event\EnterEvent;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\LeaveEvent;
use Symfony\Component\Workflow\Event\TransitionEvent;

class WorkflowSubscriber implements EventSubscriberInterface
{

    /** @var WorkflowManager */
    private $manager;

    /** @var AuthorizationCheckerInterface */
    private $authChecker;

    /** @var AuthenticationTrustResolverInterface */
    private $trustResolver;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /** @var \Doctrine\Persistence\ManagerRegistry */
    private $doctrine;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        \Doctrine\Persistence\ManagerRegistry $doctrine,
        AuthorizationCheckerInterface $authChecker,
        WorkflowManager $manager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->authChecker = $authChecker;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param AuthenticationTrustResolverInterface $trustResolver
     * @required
     */
    public function setTrustResolver(AuthenticationTrustResolverInterface $trustResolver = null): void
    {
        $this->trustResolver = $trustResolver ?: new AuthenticationTrustResolver(AnonymousToken::class, RememberMeToken::class);
    }

    public function onGuard(GuardEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());

        if (!$transition) {
            return;
        }

        /** @var EventDefinition $eventDefinition */
        foreach ($transition->getEventDefinitions() as $eventDefinition) {

            if (!$eventDefinition->isActive()) {
                continue;
            }
            if ($eventDefinition->getEventName() !== EventDefinition::GUARD) {
                continue;
            }

            $eventHandler = $this->manager->getEventHandler($eventDefinition);

            if ($eventHandler) {
                $event->setBlocked($eventHandler->run($event->getSubject(), $eventDefinition));
            }
        }


    }


    public function onTransition(TransitionEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), EventDefinition::TRANSITION);
    }

    public function onCompleted(CompletedEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), EventDefinition::COMPLETED);
    }

    public function onAnnounce(AnnounceEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), EventDefinition::ANNOUNCE);
    }


    public function onLeave(LeaveEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), EventDefinition::LEAVE);
    }


    public function onEnter(EnterEvent $event)
    {
        $transition = $event->getTransition();
        $places = $transition->getTos();

        foreach ($places as $placeItem) {
            /** @var Transition $transition */
            $placeMetaData = $event->getWorkflow()->getMetadataStore()->getPlaceMetadata($placeItem);
            $place = $placeMetaData['data'];
            $this->processPlace($place, $event->getSubject(), EventDefinition::ENTER);

            $workflowLog = new WorkflowLog($event->getSubject());
            $workflowLog->setPlace($place);
            $this->doctrine->getManager()->persist($workflowLog);
            $this->doctrine->getManager()->flush();
        }
    }

    public function onEntered(EnteredEvent $event)
    {
        /** @var \Symfony\Component\Workflow\Workflow $workflow */
        $workflow = $event->getWorkflow();
        $wwdWorkflow = $this->manager->getWorkflow($workflow);
        foreach ($event->getMarking()->getPlaces() as $place => $val) {
            $wwdPlace = $this->manager->getPlace($place);
            $this->processPlace($wwdPlace, $event->getSubject(), EventDefinition::ENTERED);
        }

    }


    public static function getSubscribedEvents()
    {
        return [
            'workflow.guard' => 'onGuard',
            'workflow.leave' => 'onLeave',
            'workflow.transition' => 'onTransition',
            'workflow.enter' => 'onEnter',
            'workflow.entered' => 'onEntered',
            'workflow.announce' => 'onAnnounce',
            'workflow.completed' => 'onCompleted',
        ];
    }

    /**
     * @param Transition $transition
     * @param mixed $subject
     * @param string $eventName
     * @return bool
     */
    private function processTransition(Transition $transition, $subject, string $eventName): bool
    {
        $result = false;
        /** @var EventDefinition $eventDefinition */
        foreach ($transition->getEventDefinitions() as $eventDefinition) {
            if ( $eventDefinition->getEventName() === $eventName && !empty($eventDefinition->getEventHandler()) ) {
                $result = $this->processEventDefinition($subject, $eventName, $eventDefinition);
            }
        }

        return $result;
    }

    /**
     * @param Place $place
     * @param mixed $subject
     * @param string $eventName
     * @return bool
     */
    private function processPlace(Place $place, $subject, string $eventName): bool
    {
        $result = false;
        /** @var EventDefinition $eventDefinition */
        foreach ($place->getEventDefinitions() as $eventDefinition) {
            $result = $this->processEventDefinition($subject, $eventName, $eventDefinition);
        }

        return $result;
    }

    /**
     * @param Workflow $workflow
     * @param mixed $subject
     * @param string $eventName
     */
    private function processWorkflow(Workflow $workflow, $subject, string $eventName): void
    {
        $o = 0;
    }

    /**
     * @param mixed $subject
     * @param string $eventName
     * @param EventDefinition $eventDefinition
     * @return bool
     */
    private function processEventDefinition($subject, string $eventName, EventDefinition $eventDefinition): bool
    {
        $result = false;
        if (!$eventDefinition->isActive()) {
            return false;
        }

        if ($eventHandler = $this->manager->getEventHandler($eventDefinition, $eventName)) {
            $success = $eventHandler->run($subject, $eventDefinition);

            $workflowLog = new WorkflowLog($subject);
            $workflowLog->setEventDefinition($eventDefinition);
            $workflowLog->setSuccess($success);
            $this->doctrine->getManager()->persist($workflowLog);
            $this->doctrine->getManager()->flush();

            $result = true;
        }
        return $result;
    }
}