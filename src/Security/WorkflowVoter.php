<?php

namespace whatwedo\WorkflowBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Security\Roles;

class WorkflowVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        if ($subject instanceof Workflow) {
            return true;
        }
        if ($subject instanceof Place) {
            return true;
        }
        if ($subject instanceof Transition) {
            return true;
        }
        if ($subject instanceof EventDefinition) {
            return true;
        }

        return in_array($attribute, Roles::getRights());
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return true;
    }


}
