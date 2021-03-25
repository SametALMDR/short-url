<?php

namespace App\Repository;

use App\Entity\UrlStats;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UrlStats|null find($id, $lockMode = null, $lockVersion = null)
 * @method UrlStats|null findOneBy(array $criteria, array $orderBy = null)
 * @method UrlStats[]    findAll()
 * @method UrlStats[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlStatsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UrlStats::class);
    }


    public function topColsByUrlId($col,$value,$max = 5)
    {
        return $this->createQueryBuilder('u')
            ->where('u.url_id = :val')
            ->setParameter('val', $value)
            ->groupBy('u.'.$col)
            ->orderBy('count','DESC')
            ->select("u.".$col.", count(u.".$col.") as count")
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }

    public function topCols($col,$max = 10)
    {
        return $this->createQueryBuilder('u')
            ->groupBy('u.'.$col)
            ->orderBy('count','DESC')
            ->select("u.".$col.", count(u.".$col.") as count")
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;
    }

    /*
    public function findOneBySomeField($value): ?UrlStats
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
