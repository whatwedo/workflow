<?php

namespace whatwedo\WorkflowBundle\EventHandler;

use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Twig\Environment;
use Twig\Loader\ChainLoader;

abstract class WorkflowSubscriberAbstract
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


    abstract public function run($subject, TransitionEventDefinition $eventDefinition): bool;

    /**
     * @param $subject
     * @param TransitionEventDefinition $eventDefinition
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\SyntaxError
     */
    protected function getTemplate($subject, TransitionEventDefinition $eventDefinition): string
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
     * @param TransitionEventDefinition $eventDefinition
     * @return mixed
     */
    protected function evaluateExpression($subject, TransitionEventDefinition $eventDefinition)
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