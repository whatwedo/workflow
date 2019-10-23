<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Message;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Twig\Environment;
use Twig\Loader\ChainLoader;

class SubjectPersister extends WorkflowSubscriberAbstract
{


    /** @var RegistryInterface */
    private $doctrine;

    /**
     * @param RegistryInterface $doctrine
     * @required
     */
    public function setDoctrine(RegistryInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    public function getExpressionHelper(): string
    {
        return 'subject';
    }


    public function run($subject, TransitionEventDefinition $eventDefinition): bool
    {

        $expression = new ExpressionLanguage(null,
            [new ExpressionLanguageProvider()]
        );


        $data = [];

        $result =  $expression->evaluate(
            $eventDefinition->getExpression(),
            [
                'data' => $data,
                'subject' => $subject,
            ]
        );



        $this->doctrine->getManager()->persist($subject);
        $this->doctrine->getManager()->flush();


        return true;
    }
}