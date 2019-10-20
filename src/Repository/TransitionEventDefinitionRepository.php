<?php

namespace whatwedo\WorkflowBundle\Repository;

use whatwedo\WorkflowBundle\Entity\TransitionEventDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TransitionEventDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransitionEventDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransitionEventDefinition[]    findAll()
 * @method TransitionEventDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransitionEventDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransitionEventDefinition::class);
    }

}
