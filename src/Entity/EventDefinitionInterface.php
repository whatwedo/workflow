<?php

namespace whatwedo\WorkflowBundle\Entity;

interface EventDefinitionInterface
{

    /**
     * @return string|null
     */
    public function getEventName(): ?string;


    /**
     * @return string|null
     */
    public function getEventSubscriber(): ?string;
    /**
     * @return string|null
     */
    public function getExpression(): ?string;

    /**
     * @return string|null
     */
    public function getTemplate(): ?string;



}
