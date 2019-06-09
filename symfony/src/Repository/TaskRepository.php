<?php

namespace App\Repository;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;

use App\Utility\Week;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @param Notebook $notebook
     * @return Task[] Returns an array of Task objects
     */
    public function findByNotebook(Notebook $notebook)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.notebook = :notebookId')
            ->setParameter('notebookId', $notebook->getNotebookId())
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param User $user
     * @param Week $week
     * @return Task[] Returns an array of Task objects
     */
    public function findAllByUserAndWeek(User $user, Week $week)
    {
        $qb = $this->createQueryBuilder('t');


        // Conditions:
        // 1. The task must have been opened on the same week or before than the query week
        // 2. The task is NOT closed, or (is closed on the same week or after the query week)
        // 3. The task is NOT canceled, or (is cancelled on the same week or after the query week)


        // OpenTask Conditions
        //  (
        //      (t.openYearNumber = queryYearNumber AND t.openWeekNumber <= queryWeekNumber)
        //      OR
        //      (t.openYearNumber < queryYearNumber)
        //  )
        // AND
        // (t.cancelTimestamp IS NULL)
        // AND
        // (t.closeTimestamp IS NULL)


        $conditionOne = $qb->expr()->orX()
            ->add($qb->expr()->andX()
                ->add('t.openYearNumber = :queryYearNumber')
                ->add('t.openWeekNumber <= :queryWeekNumber'))
            ->add('t.openYearNumber < :queryYearNumber');

        $conditionTwo = $qb->expr()->orX()
            ->add('t.closeTimestamp IS NULL')
            ->add($qb->expr()->orX()
                ->add($qb->expr()->andX()
                    ->add('t.closeYearNumber = :queryYearNumber')
                    ->add('t.closeWeekNumber >= :queryWeekNumber'))
                ->add('t.closeYearNumber > :queryYearNumber'));

        $conditionThree = $qb->expr()->orX()
            ->add('t.cancelTimestamp IS NULL')
            ->add($qb->expr()->orX()
                ->add($qb->expr()->andX()
                    ->add('t.cancelYearNumber = :queryYearNumber')
                    ->add('t.cancelWeekNumber >= :queryWeekNumber'))
                ->add('t.cancelYearNumber > :queryYearNumber'));

        // Consolidate Conditions
        $allConditions = $qb->expr()->andX()
            ->add($conditionOne)
            ->add($conditionTwo)
            ->add($conditionThree);

        $qb
            ->innerJoin('t.notebook', 'n')
            ->where('n.user = :userId')
            ->andWhere($allConditions)
            ->setParameter('userId', $user->getUserId())
            ->setParameter('queryYearNumber', $week->getYear())
            ->setParameter('queryWeekNumber', $week->getWeek())
        ;

        return $qb->getQuery()->getResult();
    }
}
