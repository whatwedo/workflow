<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\EventDefinition;

interface TransitionGuardInterface
{
    public function isBlcoked(EventDefinition $eventDefinition): bool;
}