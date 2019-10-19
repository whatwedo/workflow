<?php


namespace whatwedo\WorkflowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait WorkflowTrait
{
    /**
     * @ORM\Column(type="object", nullable=true)
     */
    private $currentPlace;


    public function getCurrentPlace()
    {
        return $this->currentPlace;
    }

    public function setCurrentPlace($currentPlace)
    {
        $this->currentPlace = $currentPlace;
    }

}