<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\SoapService;
use Zend\Soap;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SoapServerController extends AbstractController
{
    public function init()
    {
        // No cache
        ini_set('soap.wsdl_cache_enable', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
    }

    /**
     * @Route("/soap", name="soap")
     */
    public function checkAction(SoapService $soapService)
    {
        $this->init();
        if(isset($_GET['wsdl'])) {
            return $this->handleWSDL($this->generateUrl('soap', array(), UrlGeneratorInterface::ABSOLUTE_URL), $soapService); 
        } else {
            return $this->handleSOAP($this->generateUrl('soap', array(), UrlGeneratorInterface::ABSOLUTE_URL), $soapService); 
        }
    }

    /**
    * return the WSDL
    */
    public function handleWSDL($uri, $soapService)
    {
        // Soap auto discover
        $autodiscover = new Soap\AutoDiscover();
        $autodiscover->setClass($soapService);
        $autodiscover->setUri($uri);

       // Response
       $response = new Response();
       $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');
       ob_start();

       // Handle Soap
       $autodiscover->handle();
       $response->setContent(ob_get_clean());
       return $response;
    }

    /**
     * execute SOAP request
     */
    public function handleSOAP($uri, $soapService)
    {
        // Soap server
        $soap = new Soap\Server(null,
            array('location' => $uri,
            'uri' => $uri,
        ));
        $soap->setClass($soapService);

        // Response
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=ISO-8859-1');

        ob_start();
        // Handle Soap
        $soap->handle();
        $response->setContent(ob_get_clean());
        return $response;
    }
}
