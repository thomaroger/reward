<?php

namespace App\Repository;

use App\Entity\Historic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Historic>
 */
class HistoricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Historic::class);
    }

        /**
         * @return Historic[] Returns an array of Historic objects
         */
        public function findByDay($child, $currentDate): array
        {
            return $this->createQueryBuilder('h')
                ->select('t.id')
                ->join('h.task', 't')
                ->andWhere('h.child = :child')
                ->andWhere('h.created_at <= :dateend')
                ->andWhere('h.created_at >= :datebegin')
                ->setParameter('child', $child->getId())
                ->setParameter('datebegin', $currentDate->format('Y-m-d')." 00:00:00")
                ->setParameter('dateend', $currentDate->format('Y-m-d')." 23:59:59")
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findOneBySomeField($value): ?Historic
    //    {
    //        return $this->createQueryBuilder('h')
    //            ->andWhere('h.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
