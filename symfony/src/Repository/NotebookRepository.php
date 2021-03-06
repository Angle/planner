<?php

namespace App\Repository;

use App\Entity\Notebook;
use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;

use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notebook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notebook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notebook[]    findAll()
 * @method Notebook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotebookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notebook::class);
    }

    /**
     * @param User $user
     * @return Notebook[] Returns an array of Notebook objects
     */
    public function findByUser($user)
    {
        return $this->createQueryBuilder('notebook')
            ->leftJoin('notebook.shareMaps', 'map', 'WITH', 'map.user = :userId')
            ->where('notebook.user = :userId')
            ->orWhere('map.user = :userId')
            ->setParameter('userId', $user->getUserId())
            ->distinct()
            ->orderBy('notebook.name', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $code
     * @return Notebook|null
     */
    public function findOneByCode($code): ?Notebook
    {
        try {
            return $this->createQueryBuilder('n')
                ->andWhere('n.code = :code')
                ->setParameter('code', $code)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}
