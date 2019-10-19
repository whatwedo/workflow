<?php

namespace whatwedo\WorkflowBundle\Entity;

interface Workflowable
{
    public function getId();
    public function getCurrentPlace();
    public function setCurrentPlace($currentPlace);
}