<?php

namespace App\Repository;

use App\Entity\Editor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Editor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Editor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Editor[]    findAll()
 * @method Editor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Editor::class);
    }

    public function findAllImproved()
    {
        $query = $this->getEntityManager()
            ->createQuery(
            'SELECT p, e, cat, com, u 
            FROM App:Editor e
            JOIN e.posts p
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
}
