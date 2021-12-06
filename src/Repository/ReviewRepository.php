<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findByText(string $value): array
    {
        return $this->getEntityManager()
            ->createQuery("SELECT r FROM App\Entity\Review r LEFT JOIN App\Entity\Comment c WITH r.id = c.review WHERE r.text LIKE :val OR c.text LIKE :val ORDER BY r.date_update DESC")
            ->setParameter('val', '%'.$value.'%')
            ->getResult();
    }

    public function findWithRating()
    {
        return $this->getEntityManager()
            ->createQuery("SELECT r,ra FROM App\Entity\Review r LEFT JOIN r.ratings ra")
            ->getResult();
    }

    public function findFullData(int $id)
    {
        return $this->getEntityManager()
            ->createQuery("SELECT r,ra,a,c,ct,li,i,t FROM App\Entity\Review r LEFT JOIN r.ratings ra LEFT JOIN r.author a LEFT JOIN r.category c LEFT JOIN r.comments ct LEFT JOIN r.likes li
LEFT JOIN r.images i LEFT JOIN r.tags t WHERE r.id = :val")
            ->setParameter('val', $id)
            ->getOneOrNullResult();
    }

    public function findByLastUpdate()
    {
        return $this->getEntityManager()
            ->createQuery("SELECT r,ra FROM App\Entity\Review r LEFT JOIN r.ratings ra ORDER BY r.date_update DESC")
            ->setMaxResults(12)
            ->getResult();
    }

    public function findByMostRating()
    {
        return $this->getEntityManager()
            ->createQuery("SELECT r, avg(ra.value) as avg FROM App\Entity\Review r LEFT JOIN App\Entity\Rating ra WITH r.id=ra.review GROUP BY r.id ORDER BY avg DESC")
            ->setMaxResults(10)
            ->getResult();
    }
    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
