# Servicios

Una aplicación está llena de objetos útiles: Un objeto "Mailer" es útil para enviar correos, el "EntityManager" para hacer operaciones con las entidades de Doctrine...

En Symfony, estos "objetos útiles" se llaman servicios, y viven dentro de un objeto especial llamado contenedor de servicios. El contenedor nos permite centralizar el modo en el que los objetos son construidos. Simplifica el desarrollo, ayuda a construir una arquitectura robusta y es muy rápido.

## El contenedor de Servicios

El contenedor de servicios actúa mediante el patrón de inyección de dependencias cuando tipamos la clase en un parámetro de entrada de una acción de un controlador o de un constructor de otro servicio.

```php
public function index(Doctrine\ORM\EntityManagerInterface $em)
{

}
```

También utiliza la inyección de dependencias si se lo pedimos directamente

```php
public function index()
{
  $em = $this->getContainer()->get('doctrine.orm.entity_manager');
  $em = $this->get('doctrine.orm.entity_manager'); // Forma abreviada
}
```

El siguiente comando nos da una lista de los servicios que tenemos disponibles:

> bin/console debug:autowiring

Se puede ejecutar el comando para buscar algo específico:

> bin/console debug:autowiring cache

Para obtener la lista completa con más detalles, tenemos otro comando:

> bin/console debug:container

NOTA: El contenedor de dependecias utiliza la técnica de lazy-loading: no instancia un servicio hasta que se pide dicho servicio. Si no se pide, no se instancia.

NOTA: Un servicio se crea una única vez. Si en varias partes de la aplicación se le pide a Symfony un mismo servicio, Symfony devolverá siempre la misma instancia del servicio.

## Creación y configuración de servicios

### El fichero services.yaml

```yml
# config/services.yaml
services:
    # default configuration for services in *this* file
    _defaults:
        # Habilita el tipado de argumentos en los métodos constructores de los servicios
        autowire: true
        # Con autoconfigure true no es necesario poner tags a los servicos. Symfony las averigua por las interfaces que implementan.
        autoconfigure: true
        # Solamente se pueden obtener servicios con $container->get() si son públicos
        public: false

    # makes classes in src/AppBundle available to be used as services
    AppBundle\:
        resource: '../../src/*'
        # you can exclude directories or files
        # but if a service is unused, it's removed anyway
        exclude: '../../src/{Entity,Migrations,Tests,Kernel.php}'
```

### autowire

Habilita el tipado de argumentos en los métodos constructores de los servicios

### arguments

Cuando un servicio necesita argumentos que no son instancias de clases sino que son valores (como un host, un username un password, etc) no queda más remedio que declarar el servicio y establecer los valores de los argumentos

Podemos declarar servicios como argumentos de otros servicios utilizando la @.

```yml
    Symfony\Component\Ldap\Ldap:
        arguments: ['@Symfony\Component\Ldap\Adapter\ExtLdap\Adapter']

    Symfony\Component\Ldap\Adapter\ExtLdap\Adapter:
        arguments:
            -   host: 138.100.191.229
                port: 636
                encryption: ssl
                options:
                    protocol_version: 3
                    referrals: false
```

### public

Solamente se pueden obtener servicios con $container->get() si dichos servicios son públicos.

### tags

A algunos servicios hay que etiquetarlos para que symfony sepa donde van a ser utilizados dentro del framework.

Por ejemplo: para crear una extensión de Twig, necesitamos crear una clase, registrarla como servicio y etiquetarla con *twig.extension*.

Otro ejemplo: para crear un voter, hay que crear una clase, registrarla como servicio y etiquetarla con security.voter.

```yml
    App\Twig\MyTwigExtension:
        tags: [twig.extension]
    app.post_voter:
        class: App\Security\EditarEventoVoter
        tags:
            - { name: security.voter }
        public: false
```

### autoconfigure

Con autoconfigure true no es necesario poner tags a los servicos. Symfony las averigua por las interfaces que implementan.

En los ejemplos anteriores, Symfony sabría que el servicio MyTwigExtension es una extensión de Twig porque la clase implementa Twig_ExtensionInterface y que el servico app.post_voter es un voter porque la clase implementa VoterInterface.

### bind

Se puede utilizar la clave **bind** para indicar argumentos concretos por nombre o por tipo:

```yaml
# config/services.yaml
services:
    _defaults:
        bind:
            # pass this value to any $adminEmail argument for any service
            # that's defined in this file (including controller arguments)
            $adminEmail: 'manager@example.com'

            # pass this service to any $requestLogger argument for any
            # service that's defined in this file
            $requestLogger: '@monolog.logger.request'

            # pass this service for any LoggerInterface type-hint for any
            # service that's defined in this file
            Psr\Log\LoggerInterface: '@monolog.logger.request'

            # optionally you can define both the name and type of the argument to match
            string $adminEmail: 'manager@example.com'
            Psr\Log\LoggerInterface $requestLogger: '@monolog.logger.request'

    # ...
```

Se puede utilizar de forma genérica en _defaults o en un servicio concreto.

### resource y exclude

La clave *resource* se utiliza para registrar de forma rápida como servicios todas las clases dentro de un directorio. El id de cada servicio es su fully-qualified class name.

La clave *exclude* se utiliza para excluir directorios.

```yml
    App\:
        resource: '../src/*'
        exclude: '../src/{Entity,Migrations,Tests,Kernel.php}'
```

### Registrar varios servicios con la misma clase

Es posible registrar varios servicios distintos que utilicen la misma clase. Basta con ponerles identificadores distintos.

```yml
services:

    site_update_manager.superadmin:
        class: AppBundle\Updates\SiteUpdateManager
        # you CAN still use autowiring: we just want to show what it looks like without
        autowire: false
        # manually wire all arguments
        arguments:
            - '@AppBundle\Service\MessageGenerator'
            - '@mailer'
            - 'superadmin@example.com'

    site_update_manager.normal_users:
        class: AppBundle\Updates\SiteUpdateManager
        autowire: false
        arguments:
            - '@AppBundle\Service\MessageGenerator'
            - '@mailer'
            - 'contact@example.com'

    # Create an alias, so that - by default - if you type-hint SiteUpdateManager,
    # the site_update_manager.superadmin will be used
    AppBundle\Updates\SiteUpdateManager: '@site_update_manager.superadmin'
```

### Configurar un servicio para que no sea compartido

```yml
services:
    App\SomeNonSharedService:
        shared: false
        # ...
```

## Tipos de inyección

### Constructor inyection

```php
namespace App\Mail;

class NewsletterManager
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // ...
}
```

```yml
services:
    App\Mail\NewsletterManager:
        arguments: ['@mailer']
```

### Setter inyection

```php
class NewsletterManager
{
    private $mailer;

    public function setMailer(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    // ...
}
```

```yml
services:
     # ...

     app.newsletter_manager:
         class: App\Mail\NewsletterManager
         calls:
             - [setMailer, ['@mailer']]
```

### Property Injection

Solamente funciona con propiedades públicas.

```php
class NewsletterManager
{
    public $mailer;

    // ...
}
```

```yaml
services:
     # ...

     app.newsletter_manager:
         class: App\Mail\NewsletterManager
         properties:
             mailer: '@mailer'
```






Nuevo en Symfony 4.1. 
=====================

Servicios Ocultos
-----------------

Muchas veces creamos servicios que no están pensados para ser utilizados por los programadores. Si al declarar un servicio, añadimos un punto (.) al inicio del identificador del servicio, symfony lo tratará como "Servicio oculto".

Lo único distinto entre los servicios ocultos y el resto de servicios es que los servicios ocultos no aparecen en el listado del comando

> bin/console debug:container

Aunque se ha creado la opción **--show-hidden** para mostarlos si lo necesitáramos:

> ./bin/console debug:container --show-hidden

