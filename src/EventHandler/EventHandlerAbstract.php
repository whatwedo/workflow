<?php

namespace whatwedo\WorkflowBundle\EventHandler;

use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Twig\Environment;
use Twig\Loader\ChainLoader;

abstract class EventHandlerAbstract implements WorkflowEventHandlerInterface
{

    /** @var Environment */
    protected $twig;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @required
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param Environment $twig
     * @required
     */
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }

    /**
     * @param mixed $subject
     * @param EventDefinition $eventDefinition
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
    protected function getTemplate($subject, EventDefinition $eventDefinition): string
    {

        $body = $this->twig
            ->createTemplate($eventDefinition->getTemplate())
            ->render(
                [
                    'subject' => $subject,
                    'user' => $this->getUser(),
                ]
            );
        return $body;
    }

    /**
     * @param mixed $subject
     * @param EventDefinition $eventDefinition
     * @return mixed
     */
    protected function evaluateExpression($subject, EventDefinition $eventDefinition)
    {
        $expression = new ExpressionLanguage(null,
            [new ExpressionLanguageProvider()]
        );

        $data = [];
        $result = $expression->evaluate(
            $eventDefinition->getExpression(),
            [
                'data' => $data,
                'subject' => $subject,
                'user' => $this->getUser(),
            ]
        );
        return $result;
    }


    /**
     * Get a user from the Security Token Storage.
     *
     * @return UserInterface|object|null
     *
     * @see TokenInterface::getUser()
     */
    protected function getUser()
    {
        if (!$this->tokenStorage) {
            return null;
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }


}