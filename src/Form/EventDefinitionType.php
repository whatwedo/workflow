<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\WorkflowEventHandler\IWorkflowSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'eventName',
                ChoiceType::class,
                [
                    'choices' => $this->getChoices()
                ]
            )
            ->add('eventSubscriber',
                EventSubscriberTypes::class
            )
            ->add('sortorder')
            ->add(
            'expression',
            null,
                [
                    'required' => false,
                    'help' => 'some Help',
                ]
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
            'data_class' => EventDefinition::class,
        ]);
    }

    /**
     * @return array
     */
    protected function getChoices(): array
    {
        return [
            EventDefinition::GUARD => EventDefinition::GUARD,
            EventDefinition::TRANSITION => EventDefinition::TRANSITION,
            EventDefinition::COMPLETED => EventDefinition::COMPLETED,
            EventDefinition::ANNOUNCE => EventDefinition::ANNOUNCE,
        ];
    }
}
