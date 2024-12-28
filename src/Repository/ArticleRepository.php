<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function getArticlesByCountOfComments(): array
    {
        return $this->createQueryBuilder('a')
                    ->join('a.comments', 'c')
                    ->groupBy('a.id')
                    ->having('COUNT(c.id) > 0')
                    ->orderBy('COUNT(c.id)', 'desc')
                    ->getQuery()
                    ->getResult();
    }

    public function getArticlesWithComments($int = 0) :array
    {
        return $this->createQueryBuilder('a')
            ->join('a.comments', 'c')
            ->groupBy('a.id')
            ->having('COUNT(c.id) >= :int')
            ->setParameter('int', $int)
            ->orderBy('COUNT(c.id)', 'desc')
            ->getQuery()
            ->getResult();

    }
}
