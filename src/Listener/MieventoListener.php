<?php

namespace App\Listener;
use Symfony\Component\EventDispatcher\Event;

class MieventoListener
{
    public function onMievento($event)
    { 
      dump($event);
    }
}