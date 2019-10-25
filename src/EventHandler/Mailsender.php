<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Twig\Environment;

class Mailsender extends EventHandlerAbstract
{
    /** @var \Swift_Mailer */
    protected $mailer;

    /**
     * @param \Swift_Mailer $mailer
     * @required
     */
    public function setMailer(\Swift_Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function run($subject, EventDefinition $eventDefinition): bool
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

    public function getExpressionHelper(): string
    {
        return '{
            subject: "The Subject",
            sender: "Sender@Email.com",
            receiver: "receiver@Email.com",
        }';
    }
}
