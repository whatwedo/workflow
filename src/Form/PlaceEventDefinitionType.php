<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\Workflow\PlaceEventDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceEventDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'eventName',
                ChoiceType::class,
                [
                    'choices' => [
                        'leave' => 'leave',
                        'enter' => 'enter',
                        'entered' => 'entered',
                    ]
                ]
            )
            ->add('sortorder')
            ->add('expression',
                null,   [ 'required' => false ])
            ->add('template',
                null,   [ 'required' => false ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PlaceEventDefinition::class,
        ]);
    }
}
