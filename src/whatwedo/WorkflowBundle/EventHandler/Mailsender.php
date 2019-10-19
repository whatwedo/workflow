<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use Twig\Environment;

class Mailsender extends WorkflowSubscriberAbstract
{

    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * @param \Swift_Mailer $mailer
     * @required
     */
    public function setMailer(\Swift_Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function run($subject, TransitionEventDefinition $eventDefinition): bool
    {

        $data = $this->evaluateExpression($subject, $eventDefinition);
        $body = $this->getTemplate($subject, $eventDefinition);

        $message = (new \Swift_Message($data['subject']))
            ->setFrom($data['sender'])
            ->setTo($data['receiver'])
            ->setBody(
                $body,
                'text/html'
            );

        $this->mailer->send($message);
        return true;
    }

}