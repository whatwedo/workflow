<?php

namespace whatwedo\WorkflowBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Query\AST\OrderByClause;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use whatwedo\WorkflowBundle\Entity\WorkflowLog;

/**
 * @method WorkflowLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkflowLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkflowLog[]    findAll()
 * @method WorkflowLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkflowLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkflowLog::class);
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        $queryBuilder = parent::createQueryBuilder($alias, $indexBy);
        $queryBuilder->orderBy('date', 'DESC');
        return $queryBuilder;
    }


    /**
     * @param $subject
     * @return mixed
     */
    public function getLastLog(Workflowable $subject): ? WorkflowLog
    {
        $id = $subject->getId();


        /** @var WorkflowLog $result */
        $result =  $this->createQueryBuilder('w')
            ->andWhere('w.subjectClass = :subjectClass')
            ->andWhere('w.subjectId = :subjectId')
            ->setParameter('subjectClass', get_class($subject))
            ->setParameter('subjectId', $id )
            ->orderBy('w.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;

        /**  */
        if ($result) {
            foreach ($result->getTransition()->getTos() as $place) {
                if ($place->getName() == $subject->getCurrentPlace()) {
                    return $result;
                }
            }
        }


        return null;
    }

}
