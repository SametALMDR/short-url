<?php

namespace App\Repository;

use App\Entity\ThemeSections;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThemeSections|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeSections|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeSections[]    findAll()
 * @method ThemeSections[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeSectionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeSections::class);
    }

    // /**
    //  * @return ThemeSections[] Returns an array of ThemeSections objects
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
    public function findOneBySomeField($value): ?ThemeSections
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
