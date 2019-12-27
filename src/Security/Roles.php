<?php

namespace whatwedo\WorkflowBundle\Security;

class Roles
{
    const WORKFLOW_ADMIN = 'WORKFLOW_ADMIN';
    const WORKFLOW_SHOW = 'WORKFLOW_SHOW';

    public static function getRights()
    {
        return [
            self::WORKFLOW_ADMIN,
            self::WORKFLOW_SHOW,
        ];
    }
}