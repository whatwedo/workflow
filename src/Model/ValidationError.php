<?php
namespace whatwedo\WorkflowBundle\Model;

class ValidationError
{
    private $type;
    private $error;

    public function __construct(string $error, string $type = 'danger')
    {
        $this->type = $type;
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
