# Ejercicios

Doctrine

// Máxima nota de cada asignaturas de un alumno
SELECT al.nombre, as.nombre, max(n.nota)
FROM nota n
  INNER JOIN asignatura as ON as.id = n.asignatura_id
  INNER JOIN alumnos_asignaturas aa ON aa.asignatura_id = as.id
  INNER JOIN alumno al ON al.id = aa.alumno_id
group by as.nombre

## Subscriber

### Enunciado

Imaginemos que nuestra aplicación symfony consiste únicamente de una API REST.

Tenemos también un servicio TokenValidatorService con un método validate($token) que nos devuelve un booleano indicando si un token es válido o no para acceder a la API.

En nuestro services.yaml tenemos activados autoconfigure y autowire.

Crear y configurar un subscriber que lance un AccessDeniedHttpException si se realiza una petición a cualquier url de la API con un token que no sea válido.

NO es necesario programar el servicio TokenValidatorService.

### Solución

Creamos una interfaz para discriminar Controllers que tienen que validar tokens 

```php
namespace App\Controller;

interface TokenAuthenticatedController
{
    // ...
}
```

Obligamos a los controladores privados a implementar esa interfaz

```php
namespace App\Controller;

use App\Controller\TokenAuthenticatedController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FooController extends AbstractController implements TokenAuthenticatedController
{

}
```

Programamos un Subscriber que escuche el evento KernelEvents::CONTROLLER y, si el controlador que se va a ejecutar implementa TokenAuthenticatedController, entonces recogemos el token y lo validamos.


```php
// src/EventSubscriber/TokenSubscriber.php
namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private $tokenValidatorService;

    public function __construct(TokenValidatorService $tokenValidatorService)
    {
        $this->tokenValidatorService = $tokenValidatorService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof TokenAuthenticatedController) {
            $token = $event->getRequest()->query->get('token');
            if (!$this->tokenValidatorService->validate($token))) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }
        }
    }

}
```

## Listener

### Enunciado

Igual que el ejercicio anterior pero con un listener en vez de con un subscriber.

NO es necesario programar el servicio TokenValidatorService.

