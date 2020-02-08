<?php

namespace whatwedo\WorkflowBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventDefinitionType extends AbstractType
{
    /** @var WorkflowManager */
    protected $manager;

    /**
     * @param WorkflowManager|null $manager
     * @required
     */
    public function setManager(WorkflowManager $manager = null)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EventDefinition $data */
        $data = $builder->getData();

        $expressionHelp = '';

        if ($data !== null) {
            if ($eventHandler = $this->manager->getEventHandler($data)) {
                $expressionHelp = $eventHandler->getExpressionHelp();
                if (!$data->getExpression()) {
                    $data->setExpression($eventHandler->getExpressionSample());
                }
                $templateHelp = $eventHandler->getTemplateHelp();
                if (!$data->getTemplate()) {
                    $data->setTemplate($eventHandler->getTemplateSample());
                }
            }
        }


        $builder
            ->add('name')
            ->add(
                'eventName',
                ChoiceType::class,
                [
                    'choices' => $this->getChoices()
                ]
            )
            ->add('eventHandler',
                EventHandlerTypes::class
            )
            ->add('sortorder')
            ->add(
            'expression',
            null,
                [
                    'required' => false,
                    'help' => $expressionHelp,
                    'attr' => [
                        'id' => 'event_definition_expression',
                        'class' => 'expression_editor',
                    ],

                ]
            )
            ->add(
                'template',
                null,
                [
                    'required' => false,
                    'help' => $templateHelp,
                    'attr' => [
                        'id' => 'event_definition_template',
                        'class' => 'template_editor'
                    ],

                ]
            )
            ->add(
                'applyOnce',
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
