<?php


namespace whatwedo\WorkflowBundle\Form;


use ReflectionClass;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\EventHandler\WorkflowSubscriberAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowEventSubscriberTypes extends AbstractType
{

    private $workflowSubscribers;

    public function __construct()
    {
        $this->workflowSubscribers = [];
    }

    public function addWorkflowSubscriber(WorkflowSubscriberAbstract $workflowSubscriber)
    {
        $klass = get_class($workflowSubscriber);
        $reflect = new ReflectionClass($klass);
        if($reflect->isSubclassOf(WorkflowSubscriberAbstract::class)) {
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