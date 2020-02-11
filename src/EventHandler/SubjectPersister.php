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

class SubjectPersister extends AbstractEventHandler
{
    /** @var \Doctrine\Persistence\ManagerRegistry */
    private $doctrine;

    /**
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine
     * @required
     */
    public function setDoctrine(\Doctrine\Persistence\ManagerRegistry $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    public function getExpressionHelp(): string
    {
        return 'subject';
    }
    public function getExpressionSample(): string
    {
        return 'subject.name = "my Name"';
    }

    public function getTemplateHelp(): string
    {
        return '';
    }

    public function getTemplateSample(): string
    {
        return '';
    }


    public function run($subject, EventDefinition $eventDefinition): bool
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

        $this->doctrine->getManager()->persist($subject);
        $this->doctrine->getManager()->flush();


        return true;
    }

    public function validateExpression(EventDefinition $eventDefinition): bool
    {
        return true;
    }

}