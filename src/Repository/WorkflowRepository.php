<?php

namespace whatwedo\WorkflowBundle\Repository;

use whatwedo\WorkflowBundle\Entity\Workflow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Workflow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Workflow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Workflow[]    findAll()
 * @method Workflow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workflow::class);
    }

    public function findBySupportedClass(string $class)
    {
        $qb = $this->createQueryBuilder('wf')
            ->where(':class in wf.supports')
            ->setParameter('class', $class);


        return $qb->getQuery()->getResult();
    }
}
