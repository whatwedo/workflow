<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\Workflow;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'Type',
                    'choices' => [
                        'workflow' => 'workflow',
                        'state_machine' => 'state_machine',
                    ],
                ]
            )
            ->add(
                'supports',
                WorkflowSupportedTypes::class
            )
            ->add('singleState')

        ;


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Workflow::class,
        ]);
    }
}
