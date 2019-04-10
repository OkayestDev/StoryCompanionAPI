<?php

namespace App\Repository;

use App\Entity\DownVote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DownVote|null find($id, $lockMode = null, $lockVersion = null)
 * @method DownVote|null findOneBy(array $criteria, array $orderBy = null)
 * @method DownVote[]    findAll()
 * @method DownVote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DownVoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DownVote::class);
    }

//    /**
//     * @return DownVote[] Returns an array of DownVote objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DownVote
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
