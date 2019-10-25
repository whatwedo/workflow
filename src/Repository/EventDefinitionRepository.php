<?php

namespace whatwedo\WorkflowBundle\Repository;

use whatwedo\WorkflowBundle\Entity\EventDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EventDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventDefinition[]    findAll()
 * @method EventDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventDefinition::class);
    }

}
