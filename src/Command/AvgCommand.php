<?php

namespace App\Command;

use App\Repository\NotaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AvgCommand extends Command
{
    public function __construct(NotaRepository $notaRepository)
    {
        $this->notaRepository = $notaRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:command')
            ->addArgument('grado',InputArgument::REQUIRED)
            ->addArgument('dateFrom',InputArgument::REQUIRED)
            ->addArgument('dateTo',InputArgument::OPTIONAL, '', 'now')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $grado = $input->getArgument('grado');
        $dateFrom = new \DateTime($input->getArgument('dateFrom'));
        $dateTo = new \DateTime($input->getArgument('dateFrom'));

        $data = $this->notaRepository->findGradoName($grado, $dateFrom, $dateTo);
        
        $table = new Table($output);
        $table
            ->setHeaders(array('Asignatura', 'Alumno', 'Media'))
            ->setRows($data)
        ;
        $table->render();

    }
}