<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * Retourne une liste de cartes différentes aléatoirement. Le nombre de cartes peut être limité.
     *
     * @param int|null $limit Nombre de cartes différentes souhaitées
     * @return Card[]
     */
    public function findRand(?int $limit = null): array
    {
        $cards = $this->findAll();

        // Si on a précisé une limite ou si on a une limite plus petite que le nombre de carte, on va faire une
        // séléction aléatoire des cartes qu'on va utiliser, sinon on récupère toutes les cartes
        if ($limit !== null && $limit < count($cards)) {
            // On mélange la récupération afin de ne récupérer qu'une partie du jeu
            // (permet d'en prendre une liste aléatoire)
            shuffle($cards);

            return array_slice($cards, 0, $limit);
        }

        return $cards;
    }

    // /**
    //  * @return Card[] Returns an array of Card objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Card
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
