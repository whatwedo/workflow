<?php


namespace whatwedo\WorkflowBundle\EventHandler;


use whatwedo\WorkflowBundle\Entity\EventDefinition;

interface WorkflowEventHandlerInterface
{
    public function run($subject, EventDefinition $eventDefinition): bool;
    public function getExpressionHelp(): string;
    public function getExpressionSample(): string;
    public function getTemplateHelp(): string;
    public function getTemplateSample(): string;
}