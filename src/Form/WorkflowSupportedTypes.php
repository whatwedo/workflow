<?php


namespace whatwedo\WorkflowBundle\Form;


use ReflectionClass;
use Symfony\Bridge\Doctrine\RegistryInterface;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use whatwedo\WorkflowBundle\EventHandler\WorkflowSubscriberAbstract;

class WorkflowSupportedTypes extends AbstractType
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

    public function __construct()
    {
        $this->workflowableEntities = [];
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = [];

        $em = $this->doctrine->getManager();
        $classes = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        foreach($classes as $klass) {
            $reflect = new ReflectionClass($klass);
            if($reflect->implementsInterface(Workflowable::class)) {
                $choices[$reflect->getShortName()] = $klass;
            }
        }

        $resolver->setDefaults([
            'label' => 'Type',
            'choices' =>
                $choices
            ,
            'multiple' => true
        ]);
    }
}
