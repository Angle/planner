<?php

namespace App\Repository;

use DateTime;
use DateTimeZone;
use DateInterval;

use App\Entity\Notebook;
use App\Entity\Task;
use App\Entity\User;

use App\Util\Week;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
    public function findAllByOwnerUserAndWeek(User $user, Week $week)
    {
        $qb = $this->createQueryBuilder('t');

        $allConditions = $this->taskTimeConditions();

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

    /**
     * @param Notebook[] $notebooks
     * @param Week $week
     * @return Task[] Returns an array of Task objects
     */
    public function findAllInNotebooksByWeek(array $notebooks, Week $week)
    {
        $qb = $this->createQueryBuilder('t');

        $allConditions = $this->taskTimeConditions();

        $notebookList = [];

        foreach ($notebooks as $n) {
            $notebookList[] = $n->getNotebookId();
        }

        $qb
            ->where('t.notebook IN (:notebookList)')
            ->innerJoin('t.notebook', 'n')
            ->andWhere($allConditions)
            ->setParameter('notebookList', $notebookList)
            ->setParameter('queryYearNumber', $week->getYear())
            ->setParameter('queryWeekNumber', $week->getWeek())
            ->orderBy('n.name', 'ASC')
            ->addOrderBy('t.concept', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Notebook[] $notebooks
     * @param DateTime $focusDate
     * @return Task[] Returns an array of Task objects
     */
    public function findAllInNotebooksByFocusDate(array $notebooks, DateTime $focusDate)
    {
        $utc = new DateTimeZone('UTC');
        $qb = $this->createQueryBuilder('t');

        $focusStart = clone $focusDate;
        $focusStart = $focusStart->setTime(0,0,0);
        $focusStart = $focusStart->setTimezone($utc);

        $focusEnd = clone $focusDate;
        $focusEnd = $focusEnd->setTime(0,0,0);
        $focusEnd = $focusEnd->add(new DateInterval('P1D'));
        $focusEnd = $focusEnd->setTimezone($utc);

        $notebookList = [];

        foreach ($notebooks as $n) {
            $notebookList[] = $n->getNotebookId();
        }

        $qb
            ->where('t.notebook IN (:notebookList)')
            ->innerJoin('t.notebook', 'n')
            ->andWhere('t.focusDate IS NOT NULL')
            ->andWhere('t.focusDate >= :focusStart')
            ->andWhere('t.focusDate < :focusEnd')
            ->setParameter('notebookList', $notebookList)
            ->setParameter('focusStart', $focusStart->format('Y-m-d H:i:s'))
            ->setParameter('focusEnd', $focusEnd->format('Y-m-d H:i:s'))
            ->orderBy('n.name', 'ASC')
            ->addOrderBy('t.concept', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    private function taskTimeConditions()
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
            ->add($qb->expr()->orX()
                ->add('t.closeYearNumber IS NULL')
                ->add('t.closeWeekNumber IS NULL'))
            ->add($qb->expr()->orX()
                ->add($qb->expr()->andX()
                    ->add('t.closeYearNumber = :queryYearNumber')
                    ->add('t.closeWeekNumber >= :queryWeekNumber'))
                ->add('t.closeYearNumber > :queryYearNumber'));

        $conditionThree = $qb->expr()->orX()
            ->add($qb->expr()->orX()
                ->add('t.cancelYearNumber IS NULL')
                ->add('t.cancelWeekNumber IS NULL'))
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

        return $allConditions;
    }
}
