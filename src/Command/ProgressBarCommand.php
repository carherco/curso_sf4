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

use Symfony\Component\Console\Helper\ProgressBar;


class ProgressBarCommand extends Command {
    
    protected function configure() {
        $this
            ->setName('app:progress')
            ->setDescription('Ejemplo de barra de progreso');   
        ;        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {

        $num_elements = 434500;
        $progressBar = new ProgressBar($output, $num_elements);

        // starts and displays the progress bar
        $progressBar->start();
        
        $redrawFrequency = round($num_elements / 1000);
        $progressBar->setRedrawFrequency($redrawFrequency);

        $i = 0;
        while ($i++ < $num_elements) {         
            usleep(200);
            $progressBar->advance();
        }

        // ensures that the progress bar is at 100%
        $progressBar->finish();
    }
    
}
