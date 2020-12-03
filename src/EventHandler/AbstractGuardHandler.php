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

abstract class AbstractGuardHandler implements TransitionGuardHandlerInterface
{

    /** @var AuthorizationCheckerInterface */
    protected $authChecker;

    /** @var AuthenticationTrustResolverInterface */
    protected $trustResolver;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->authChecker = $authChecker;
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

    public function hasExpression(): bool
    {
        return true;
    }

    public function hasTemplate(): bool
    {
        return false;
    }

}