<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Soap;

class SoapClientController extends AbstractController
{
    /**
     * @Route("/soap-client", name="soap-client")
     */
    public function index()
    {
      $client = new Soap\Client("http://127.0.0.1:8001/soap?wsdl", array('cache_wsdl' => WSDL_CACHE_NONE));
      $response = $client->method1('un texto cualquiera');
      var_dump($response); exit;

      return new Response();
    }
}
