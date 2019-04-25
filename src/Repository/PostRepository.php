<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllImproved()
    {
        $query = $this->getEntityManager()
            ->createQuery(
            'SELECT p, e, cat, com, u 
            FROM App:Post p
            JOIN p.author e
            JOIN p.categories cat
            JOIN p.comments com
            JOIN com.author u '
        );

        try {
            return $query->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }

    public function findAllMultiStep()
    {
        $query1 = $this->getEntityManager()
            ->createQuery('
                SELECT p, e 
                FROM App:Post p
                JOIN p.author e
            ');
        
        $query2 = $this->getEntityManager()
            ->createQuery('
                SELECT PARTIAL p.{id}, cat
                FROM App:Post p
                JOIN p.categories cat
            ');

        $query3 = $this->getEntityManager()
            ->createQuery('
                SELECT PARTIAL p.{id}, com, u
                FROM App:Post p
                JOIN p.comments com
                JOIN com.author u
            ');

        // Y probar con fetch="EAGER" en com.author
        // $query3 = $this->getEntityManager()
        //     ->createQuery('
        //         SELECT PARTIAL p.{id}, com
        //         FROM App:Post p
        //         JOIN p.comments com
        //     ');

        // Y probar a pedirlos del revés (no sirve)
        // $query3 = $this->getEntityManager()
        //     ->createQuery('
        //         SELECT PARTIAL p.{id}, com
        //         FROM App:Comment com
        //         JOIN com.post p
        //     ');

        try {
            $posts = $query1->getResult();
            $query2->getResult();
            $query3->getResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $posts = null;
        }

        return $posts;
    }

}
