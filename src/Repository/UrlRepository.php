<?php

namespace App\Repository;

use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }


    public function findByUid($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.user_id = :val')
            ->setParameter('val', $value)
            ->orderBy('u.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getTotalCount()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function topUsers()
    {
        return $this->createQueryBuilder('u')
            ->select('count(u.id) as count, u.user_id')
            ->groupBy('u.user_id')
            ->orderBy('count','DESC')
            ->getQuery()
            ->getResult();
    }

    public function topClicked()
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.url_hash')
            ->orderBy('u.click_count','DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function topDomains()
    {
        return $this->createQueryBuilder('u')
            ->select('u.id, u.url')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Url
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
