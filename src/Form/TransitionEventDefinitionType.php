<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use whatwedo\WorkflowBundle\WorkflowEventHandler\IWorkflowSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransitionEventDefinitionType extends AbstractType
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
                        TransitionEventDefinition::GUARD => TransitionEventDefinition::GUARD,
                        TransitionEventDefinition::TRANSITION => TransitionEventDefinition::TRANSITION,
                        TransitionEventDefinition::COMPLETED => TransitionEventDefinition::COMPLETED,
                        TransitionEventDefinition::ANNOUNCE => TransitionEventDefinition::ANNOUNCE,
                        TransitionEventDefinition::ENTERED => TransitionEventDefinition::ENTERED,
                        TransitionEventDefinition::ENTER => TransitionEventDefinition::ENTER,
                        TransitionEventDefinition::LEAVE => TransitionEventDefinition::LEAVE,
                    ]
                ]
            )
            ->add('eventSubscriber',
                WorkflowEventSubscriberTypes::class
//                TextType::class
            )
            ->add('sortorder')
            ->add(
            'expression',
            null,
                [ 'required' => false ]
            )
            ->add(
                'template',
                null,
                [ 'required' => false ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransitionEventDefinition::class,
        ]);
    }
}
