<?php

namespace whatwedo\WorkflowBundle\Form;

use Symfony\Bridge\Doctrine\RegistryInterface;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Form\DataTransformer\EntityToValueTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransitionType extends AbstractType
{
    /** @var RegistryInterface */
    private $doctirine;

    /**
     * @param RegistryInterface $doctirine
     * @required
     */
    public function setDoctirine(RegistryInterface $doctirine): void
    {
        $this->doctirine = $doctirine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Transition $data */
        $data = $builder->getData();
        $builder
            ->add('name')
            ->add(
                'workflow',
                HiddenType::class,
                    [
                        'property_path' => 'id'
                    ]
                )
            ->add(
                'froms',
                    EntityType::class,
                    [
                        'choices' => $data->getWorkflow()->getPlaces(),
                        'class' => Place::class,
                        'multiple' => true,
                    ]
                )
            ->add('tos'
                ,
                EntityType::class,
                [
                    'choices' => $data->getWorkflow()->getPlaces(),
                    'class' => Place::class,
                    'multiple' => true,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transition::class,
        ]);
    }
}
