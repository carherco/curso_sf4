<?php

namespace App\Service;

class SoapService
{
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Devuelve un string
     * 
     * @param string $some_data
     * @return string
     */
    public function method1($some_data)
    {
        // $message = new \Swift_Message('method1 Service')
        //     ->setTo('me@example.com')
        //     ->setBody($some_data);

        // $this->mailer->send($message);

        return 'service method1 executed with data: , '.$some_data;
    }
}