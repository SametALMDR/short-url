<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }


    public function findByRead($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.is_read = :val')
            ->setParameter('val', $value)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByAnswered($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.is_answered = :val')
            ->setParameter('val', $value)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getUnreadedCount()
    {
        return $this->createQueryBuilder('c')
            ->where('c.is_read = 0')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTotalCount()
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }


    /*
    public function findOneBySomeField($value): ?Contact
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
