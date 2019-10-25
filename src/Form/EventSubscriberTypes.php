<?php


namespace whatwedo\WorkflowBundle\Form;


use ReflectionClass;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\EventHandler\EventHandlerAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSubscriberTypes extends AbstractType
{
    private $workflowSubscribers;

    public function __construct()
    {
        $this->workflowSubscribers = [];
    }

    public function addWorkflowSubscriber(EventHandlerAbstract $workflowSubscriber)
    {
        $klass = get_class($workflowSubscriber);
        $reflect = new ReflectionClass($klass);
        if($reflect->isSubclassOf(EventHandlerAbstract::class)) {
            $this->workflowSubscribers[$klass] = $klass;
        }
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Event Subscriber',
            'choices' =>
                $this->workflowSubscribers
            ,
            'multiple' => false,
            'required' => false,
        ]);
    }

}