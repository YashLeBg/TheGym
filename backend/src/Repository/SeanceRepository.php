<?php

namespace App\Repository;

use App\Entity\Coach;
use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    /**
     * Trouve toutes les séances validées (terminées) pour un coach dans une période donnée
     * 
     * @param Coach $coach Le coach concerné
     * @param \DateTimeInterface $startDate Date de début de la période
     * @param \DateTimeInterface $endDate Date de fin de la période
     * @return Seance[] Tableau des séances validées
     */
    public function findValidatedSessionsByCoachAndPeriod(
        Coach $coach,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.coach = :coach')
            ->andWhere('s.date_heure BETWEEN :startDate AND :endDate')
            ->andWhere('s.statut = :statut')
            ->setParameter('coach', $coach)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('statut', 'terminee')
            ->orderBy('s.date_heure', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Seance[] Returns an array of Seance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Seance
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
