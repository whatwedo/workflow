<?php

namespace whatwedo\WorkflowBundle\Repository;

use whatwedo\WorkflowBundle\Entity\Workflow\Transition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Transition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transition[]    findAll()
 * @method Transition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transition::class);
    }

    // /**
    //  * @return Transition[] Returns an array of Transition objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transition
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
