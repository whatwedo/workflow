<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\EventDefinition;

interface WorkflowEventHandlerInterface
{
    public function run($subject, EventDefinition $eventDefinition): bool;
    public function getExpressionHelper(): string;
}