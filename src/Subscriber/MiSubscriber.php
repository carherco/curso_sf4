<?php
namespace App\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MiSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'mi_evento' => 'mifuncion',
        );
    }

    public function mifuncion($event)
    {
        dump($event);
    }
}