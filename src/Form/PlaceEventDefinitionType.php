<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\PlaceEventDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceEventDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PlaceEventDefinition $data */
        $data = $builder->getData();

        $builder
            ->add('name')
            ->add(
                'eventName',
                ChoiceType::class,
                [
                    'choices' => [
                        PlaceEventDefinition::LEAVE   => PlaceEventDefinition::LEAVE,
                        PlaceEventDefinition::ENTER   => PlaceEventDefinition::ENTER,
                        PlaceEventDefinition::ENTERED => PlaceEventDefinition::ENTERED,
                        PlaceEventDefinition::CHECK   => PlaceEventDefinition::CHECK,
                    ]
                ]
            )
            ->add('eventSubscriber',
                WorkflowEventSubscriberTypes::class
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
