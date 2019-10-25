<?php

namespace whatwedo\WorkflowBundle\EventHandler;

use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Twig\Environment;
use Twig\Loader\ChainLoader;

abstract class EventHandlerAbstract
{

    /** @var Environment */
    protected $twig;


    /**
     * @param Environment $twig
     * @required
     */
    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
    }


    abstract public function run($subject, EventDefinition $eventDefinition): bool;
    abstract public function getExpressionHelper(): string;

    /**
     * @param $subject
     * @param EventDefinition $eventDefinition
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
    protected function getTemplate($subject, EventDefinition $eventDefinition): string
    {
        $twig = new Environment(new ChainLoader());
        $twig->setCache(false);


        $body = $twig
            ->createTemplate($eventDefinition->getTemplate())
            ->render(
                [
                    'name' => 'asdfasd',
                    'subject' => $subject,
                ]
            );
        return $body;
    }

    /**
     * @param $subject
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
            ]
        );
        return $result;
    }

}