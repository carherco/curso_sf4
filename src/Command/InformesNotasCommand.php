<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Grado;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;


/**
 * Description of InformesNotasCommand
 * 
 * bin/console informes:notas "Ingeniería de montes" 2016-09-01 2017-08-31
 *
 * @author carlos
 */
class InformesNotasCommand extends Command {
    
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }
    
    protected function configure() {
        $this
            ->setName('informes:notas')
            ->setDescription('Listado de las notas medias de las asignaturas de un grado entre dos fechas')
            ->addArgument('grado', InputArgument::REQUIRED, 'Nombre del grado')
            ->addArgument('dateFrom', InputArgument::REQUIRED, 'Fecha de inicio (yyyy-mm-dd)')
            ->addArgument('dateTo', InputArgument::REQUIRED, 'Fecha fin (yyyy-mm-dd)')
        ;        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $grado = $input->getArgument('grado');
        $dateFrom = $input->getArgument('dateFrom');
        $dateTo = $input->getArgument('dateTo');
        
        $asignaturas = $this->entityManager
                        ->getRepository(Grado::class)
                        ->getAverageNoteByGrade($grado, $dateFrom, $dateTo)
        ;
        
        $rows = [];
        foreach($asignaturas as $asignatura) {
            $rows[] = [ $asignatura['nombre'], $asignatura['nota_media'] ];
        }
        
        $table = new Table($output);
        
        if(empty($rows)) {
            $table->setHeaders(array('No se encontraron registros'));
        } else {
            $textoHeader = 'Grado: '.$grado.' ---- Fecha: '.$dateFrom.' / '.$dateTo;
            $table->setHeaders( [ 
                [new TableCell($textoHeader, ['colspan' => 2])],
                ['Asignatura', 'NotaMedia']
            ] );
            
            $table->setRows($rows);
        }
        
        
        $table->render();
    }
    
}
