<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 02.01.19
 * Time: 17:01
 */

namespace whatwedo\WorkflowBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinitionInterface;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\PlaceEventDefinition;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Entity\WorkflowLog;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
    /** @var ContainerInterface */
    protected $container;

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

    /** @var RegistryInterface */
    private $doctrine;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @required
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param ContainerInterface|null $container
     * @required
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * @param RegistryInterface $doctrine
     * @required
     */
    public function setDoctrine(RegistryInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param AuthorizationCheckerInterface $authChecker
     * @required
     */
    public function setAuthChecker(AuthorizationCheckerInterface $authChecker): void
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @param WorkflowManager $manager
     * @required
     */
    public function setManager(WorkflowManager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * @param AuthenticationTrustResolverInterface $trustResolver
     * @required
     */
    public function setTrustResolver(AuthenticationTrustResolverInterface $trustResolver = null): void
    {
        $this->trustResolver = $trustResolver ?: new AuthenticationTrustResolver(AnonymousToken::class, RememberMeToken::class);
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     * @required
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onGuard(GuardEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());



        /** @var TransitionEventDefinition $eventDefinition */
        foreach ($transition->getEventDefinitions() as $eventDefinition) {
            if (empty($eventDefinition->getEventSubscriber()) && $eventDefinition->getEventName() === TransitionEventDefinition::GUARD) {
                // do work
                if (!empty($eventDefinition->getExpression())) {
                    $expression = new ExpressionLanguage(null,
                        [new ExpressionLanguageProvider()]
                    );

                    $event->setBlocked(
                        ! $expression->evaluate(
                            $eventDefinition->getExpression(),
                            [
                                'subject' => $event->getSubject(),
                                'transition' => $event->getTransition(),
                                'workflow' => $event->getWorkflow(),
                                'auth_checker' => $this->authChecker,
                                'trust_resolver' => $this->trustResolver,
                                'token' => $this->tokenStorage,
                            ]
                        )
                    );
                }
            } else {
              $o = 0;
            }
        }
    }


    public function onTransition(TransitionEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), TransitionEventDefinition::TRANSITION);
    }

    public function onCompleted(CompletedEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), TransitionEventDefinition::COMPLETED);
    }

    public function onAnnounce(AnnounceEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), TransitionEventDefinition::ANNOUNCE);
    }


    public function onLeave(LeaveEvent $event)
    {
        /** @var Transition $transition */
        $transition = $event->getMetadata('data', $event->getTransition());
        $this->processTransition($transition, $event->getSubject(), PlaceEventDefinition::LEAVE);
    }


    public function onEnter(EnterEvent $event)
    {
        $transition = $event->getTransition();
        $places = $transition->getTos();

        foreach ($places as $placeItem) {
            /** @var Transition $transition */
            $placeMetaData = $event->getWorkflow()->getMetadataStore()->getPlaceMetadata($placeItem);
            $place = $placeMetaData['data'];
            $this->processPlace($place, $event->getSubject(), PlaceEventDefinition::ENTER);

            $workflowLog = new WorkflowLog($event->getSubject(), null, $place);
            $this->doctrine->getManager()->persist($workflowLog);
            $this->doctrine->getManager()->flush();
        }
    }

    public function onEntered(EnteredEvent $event)
    {
        /** @var Workflow $workflow */
        $workflow = $event->getMetadata('data', null);
        $this->processWorkflow($workflow, $event->getSubject(), PlaceEventDefinition::ENTERED);
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
     * @param $eventName
     * @return bool
     */
    private function processTransition(Transition $transition, $subject, $eventName): bool
    {
        $result = false;
        /** @var EventDefinitionInterface $eventDefinition */
        foreach ($transition->getEventDefinitions() as $eventDefinition) {
            if ( $eventDefinition->getEventName() === $eventName && !empty($eventDefinition->getEventSubscriber()) ) {
                $eventSubscriberClass = $eventDefinition->getEventSubscriber();
                /** @var IWorkflowSubscriber $workflowSubscriber */
                $workflowSubscriber = $this->container->get($eventSubscriberClass);
                $success = $workflowSubscriber->run($subject, $eventDefinition);

                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param Transition $place
     * @param $eventName
     * @return bool
     */
    private function processPlace(Place $place, $subject, $eventName): bool
    {
        $result = false;
        /** @var EventDefinitionInterface $eventDefinition */
        foreach ($place->getEventDefinitions() as $eventDefinition) {
            if ( $eventDefinition->getEventName() === $eventName && !empty($eventDefinition->getEventSubscriber()) ) {
                $eventSubscriberClass = $eventDefinition->getEventSubscriber();
                /** @var IWorkflowSubscriber $workflowSubscriber */
                $workflowSubscriber = $this->container->get($eventSubscriberClass);
                $success = $workflowSubscriber->run($subject, $eventDefinition);

                $result = true;
            }
        }

        return $result;
    }

    /**
     * @param Transition $transition
     * @param $eventName
     */
    private function processWorkflow(Workflow $workflow, $subject, $eventName): void
    {
        $o = 0;
    }
}