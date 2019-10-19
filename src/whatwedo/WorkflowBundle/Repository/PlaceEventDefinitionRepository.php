<?php

namespace whatwedo\WorkflowBundle\Repository;

use whatwedo\WorkflowBundle\Entity\Workflow\PlaceEventDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PlaceEventDefinition|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaceEventDefinition|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaceEventDefinition[]    findAll()
 * @method PlaceEventDefinition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceEventDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaceEventDefinition::class);
    }

}
