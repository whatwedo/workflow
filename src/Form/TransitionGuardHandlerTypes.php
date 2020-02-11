<?php


namespace whatwedo\WorkflowBundle\Form;


use ReflectionClass;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\EventHandler\EventHandlerAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use whatwedo\WorkflowBundle\EventHandler\TransitionGuardHandlerInterface;
use whatwedo\WorkflowBundle\EventHandler\WorkflowEventHandlerInterface;

class TransitionGuardHandlerTypes extends AbstractType
{
    /** @var array|TransitionGuardHandlerInterface  */
    private $guardHandler;

    public function __construct()
    {
        $this->guardHandler = [];
    }

    public function addGuardHandler(TransitionGuardHandlerInterface $transitionGuardHandler)
    {
        $klass = get_class($transitionGuardHandler);
        $reflect = new ReflectionClass($klass);
        if($reflect->implementsInterface(TransitionGuardHandlerInterface::class)) {
            $this->guardHandler[$klass] = $klass;
        }
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Guard Subscriber',
            'choices' =>
                $this->guardHandler
            ,
            'multiple' => false,
            'required' => false,
        ]);
    }

}