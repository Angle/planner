<?php

namespace App\Repository;

use App\Entity\ShareMap;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

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
}
