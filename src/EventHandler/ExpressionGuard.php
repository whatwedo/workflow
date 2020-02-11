<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class ExpressionGuard extends AbstractGuard
{

    public function isBlcoked(EventDefinition $eventDefinition): bool
    {
        $result = false;
        if (!empty($eventDefinition->getExpression())) {
            $expression = new ExpressionLanguage(null,
                [new ExpressionLanguageProvider()]
            );

            $result = $expression->evaluate(
                    $eventDefinition->getExpression(),
                    [
                        'subject' => $event->getSubject(),
                        'transition' => $event->getTransition(),
                        'workflow' => $event->getWorkflow(),
                        'auth_checker' => $this->authChecker,
                        'trust_resolver' => $this->trustResolver,
                        'token' => $this->tokenStorage,
                    ]
            );
        }

        return $result;
    }

}