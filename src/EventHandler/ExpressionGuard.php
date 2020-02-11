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

class ExpressionGuard extends AbstractGuardHandler implements TransitionGuardHandlerInterface
{

    public function run($subject, EventDefinition $eventDefinition): bool
    {
        $result = false;
        if (!empty($eventDefinition->getExpression())) {
            $expression = new ExpressionLanguage(null,
                [new ExpressionLanguageProvider()]
            );

            $result = $expression->evaluate(
                $eventDefinition->getExpression(),
                [
                    'subject' => $subject,
                    'auth_checker' => $this->authChecker,
                    'trust_resolver' => $this->trustResolver,
                    'token' => $this->tokenStorage,
                ]
            );
        }

        return $result;
    }

    public function validateExpression(EventDefinition $eventDefinition): bool
    {
        return true;
    }

    public function getExpressionHelp(): string
    {
        return 'true = blocks the guard, false = ok';
    }


    public function getExpressionSample(): string
    {
        return '';
    }

    public function getTemplateHelp(): string
    {
        return '';
    }

    public function getTemplateSample(): string
    {
        return '';
    }

    public function validateTemplate(EventDefinition $eventDefinition): bool
    {
        return true;
    }


}