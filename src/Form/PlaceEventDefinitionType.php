<?php

namespace whatwedo\WorkflowBundle\Form;

use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceEventDefinitionType extends EventDefinitionType
{
    /**
     * @return array
     */
    protected function getChoices(): array
    {
        return [
            EventDefinition::LEAVE => EventDefinition::LEAVE,
            EventDefinition::ENTER => EventDefinition::ENTER,
            EventDefinition::ENTERED => EventDefinition::ENTERED,
            EventDefinition::CHECK => EventDefinition::CHECK,
        ];
    }
}
