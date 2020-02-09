<?php

namespace whatwedo\WorkflowBundle\Model;

class DummySubject
{
    public function __get($field) {
        return $field;
    }

    public function __set($field, $value) {
    }

    public function __toString()
    {
        return 'dummy';
    }

    public function __call($name, $arguments)
    {
        if ($name == 'id') {
            return 123;
        }
        return $this;
    }

}