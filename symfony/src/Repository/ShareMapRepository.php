<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Notebook;
use App\Entity\ShareMap;
use App\Entity\User;

/**
 * @method ShareMap|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShareMap|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShareMap[]    findAll()
 * @method ShareMap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShareMapRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShareMap::class);
    }

    /**
     * @param User $user
     * @return ShareMap[] Returns an array of Notebook objects
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $user->getUserId())
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param string $email
     * @return ShareMap[] Returns an array of Notebook objects
     */
    public function findByInviteEmail(string $email)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.inviteEmail = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getResult()
            ;
    }
}
