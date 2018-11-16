# Cómo crear un servicio SOAP

## Requisitos

Para trabajar con SOAP es necesario tener instalada la extensión **PHP SOAP** de php.

http://php.net/manual/es/soap.installation.php

## Librerías para trabajar con SOAP

Se puede programar sin librerías, construyéndonos y exponiendo nuestros propios WSDLs pero lo lógico es utilizar librerías contrastadas.

Zend SOAP: https://framework.zend.com/manual/2.4/en/modules/zend.soap.server.html

NuSOAP: https://sourceforge.net/projects/nusoap/

Vamos a trabajar con Zend:

> composer require zendframework/zend-soap

## Programación del servidor

### Creación de una clase con los métodos del servicio

La librería de Zend viene con utilidades que transforman los métodos de una clase servicio en el correspondiente WSDL de forma automática. 

Pongamos que tenemos por ejemplo esta clase (servicio):

```php
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
        $message = new \Swift_Message('method1 Service')
            ->setTo('me@example.com')
            ->setBody($some_data);

        $this->mailer->send($message);

        return 'service method1 executed with data: , '.$some_data;
    }
}
```

Esta clase define un servicio (método) method1 al que se le pasa un argumento de tipo string y devuelve una salida de tipo string tras realizar el envío de un correo.

Es obligatorio, documentar las funciones con @param y @return para que se pueda construir el WSDL.

### Creación del controlador que expondrá los servicios

```php
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

```

Nuestro controlador expone una ruta **/soap**.

Cuando se llame a esta ruta con el argumento **?wsdl**, se debe devolver al cliente el WSDL con la definición de nuestros servicios.

Lo podemos comprobar a mano:

http://127.0.0.1:8000/soap?wsdl

```xml
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:tns="/soap" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" name="SoapService" targetNamespace="/soap">
  <types>
    <xsd:schema targetNamespace="/soap"/>
  </types>
  <portType name="SoapServicePort">
    <operation name="method1">
      <documentation>method1</documentation>
      <input message="tns:method1In"/>
    </operation>
  </portType>
  <binding name="SoapServiceBinding" type="tns:SoapServicePort">
    <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="method1">
      <soap:operation soapAction="/soap#method1"/>
      <input>
        <soap:body use="encoded" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="/soap"/>
      </input>
    </operation>
  </binding>
  <service name="SoapServiceService">
    <port name="SoapServicePort" binding="tns:SoapServiceBinding">
      <soap:address location="/soap"/>
    </port>
  </service>
  <message name="method1In">
    <part name="some_data" type="xsd:anyType"/>
  </message>
</definitions>
```

Pero cuando se llame sin el argumento ?wsdl, deberá procesar la petición llamando al servicio correspondiente.

## Programación del cliente

La parte del cliente es todavía más sencilla:

Se crea una instancia de Zend\Soap\Client a la que se le pasa la url del wsdl. Al obtener y analizar el wsdl, la instancia de Zend\Soap\Client adquiere métodos que se corresponden con los servicios expuestos. De esta forma utilizaremos los servicios SOAP como si fueran métodos normales y corrientes.

```php
/**
 * @Route("/soap-client", name="soap-client")
 */
public function index()
{
  $client = new Soap\Client("http://127.0.0.1:8000/soap?wsdl", array('cache_wsdl' => WSDL_CACHE_NONE));
  $response = $client->method1('un texto cualquiera');
  var_dump($response); exit;

  return new Response();
}
```

La librería de Zend se encarga de construir el WSDL, transformar las llamadas del cliente en peticiones SOAP con formato XML correspondiente, recoger la petición del cliente y transformarla en la llamada al método del servicio correspondiente. Transformar la salida del método del servicio en una respuesta SOAP con formato XML y enviarla al cliente. Y en el cliente, volver a transformar la respuesta en el valor correspondiente.