<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Message;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguageProvider;
use Twig\Environment;
use Twig\Loader\ChainLoader;

class SubjectPersister extends EventHandlerAbstract
{
    /** @var \Doctrine\Common\Persistence\ManagerRegistry */
    private $doctrine;

    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $doctrine
     * @required
     */
    public function setDoctrine(\Doctrine\Common\Persistence\ManagerRegistry $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    public function getExpressionHelper(): string
    {
        return 'subject';
    }


    public function run($subject, EventDefinition $eventDefinition): bool
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