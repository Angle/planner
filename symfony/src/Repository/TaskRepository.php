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
        $conditionOneTaskOrX = $qb->expr()->orX();
        $conditionOneOpenTaskAndX = $qb->expr()->andX();
        $conditionOneOpenTaskAndX->add('t.openYearNumber = :queryYearNumber');
        $conditionOneOpenTaskAndX->add('t.openWeekNumber <= :queryWeekNumber');
        $conditionOneTaskOrX->add($conditionOneOpenTaskAndX);
        $conditionOneTaskOrX->add('t.openYearNumber < :queryYearNumber');

        // Open Task Consolidate
        $globalOpenTaskAndX = $qb->expr()->andX();
        $globalOpenTaskAndX->add('t.cancelTimestamp IS NULL');
        $globalOpenTaskAndX->add('t.closeTimestamp IS NULL');
        $globalOpenTaskAndX->add($conditionOneTaskOrX);

        // Close Task Condition
        // (
        //      t.closeYearNumber = queryYearNumber AND t.closeWeekNumber = queryWeekNumber
        // )
        $globalCloseTaskAndX = $qb->expr()->andX();
        $globalCloseTaskAndX->add('t.closeYearNumber = :queryYearNumber');
        $globalCloseTaskAndX->add('t.closeWeekNumber = :queryWeekNumber');

        // Cancel Task Condition
        // (
        //      t.cancelYearNumber = queryYearNumber AND t.canceleWeekNumber = queryWeekNumber
        // )
        $globalCancelTaskAndX = $qb->expr()->andX();
        $globalCancelTaskAndX->add('t.cancelYearNumber = :queryYearNumber');
        $globalCancelTaskAndX->add('t.cancelWeekNumber = :queryWeekNumber');

        // Consolidate Conditions
        $globalOrX = $qb->expr()->orX();
        $globalOrX->add($globalOpenTaskAndX);
        $globalOrX->add($globalCloseTaskAndX);
        $globalOrX->add($globalCancelTaskAndX);

        $qb
            ->innerJoin('r.notebook', 'n')
            ->where('n.user = :userId')
            ->andWhere($globalOrX)
            ->setParameter('userId', $user->getUserId())
            ->setParameter('queryYearNumber', $week->getYear())
            ->setParameter('queryWeekNumber', $week->getWeek())
        ;

        return $qb->getQuery()->getResult();
    }
}
