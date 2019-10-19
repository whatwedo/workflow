<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Workflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{

    /** @var DoctrineHelper */
    private $doctirineHelper;

    /**
     * @param DoctrineHelper $doctirineHelper
     * @required
     */
    public function setDoctirineHelper(DoctrineHelper $doctirineHelper): void
    {
        $this->doctirineHelper = $doctirineHelper;
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'workflow',
                HiddenType::class
            )
            ->add('name')
            ->add('limit')
        ;

        $builder->get('workflow')->addModelTransformer(new EntityToValueTransformer($this->doctirineHelper->getRepository(Workflow::class)));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
