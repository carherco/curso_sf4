<?php

namespace App\Repository;

use App\Entity\Grado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class GradoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Grado::class);
    }
    
    public function getAverageNoteByGrade($grado, $fechaIni, $fechaFin)
	{
        return $this->createQueryBuilder('g')
                ->select('a.nombre','avg(n.nota) as nota_media')
                ->join('g.asignaturas','a')
                ->join('a.notas','n')
                ->where('g.nombre = :grado')
                ->andWhere('n.fecha BETWEEN :fechaIni AND :fechaFin')
                ->groupBy('n.asignatura')
                ->setParameter('grado', $grado)
                ->setParameter('fechaIni', $fechaIni)
                ->setParameter('fechaFin', $fechaFin)
                ->getQuery()
                ->execute();
   }


}
