# Envío de Correos

Symfony gestiona el envío de correos a través del bundle **SwiftMailerBundle**.

Para instalarlo con Flex:

> composer require mailer

## Configuración

- url

Permite poner la configuración completa utilizando una URL de tipo DSN:

```yml
smtp://user:pass@host:port/?timeout=60&encryption=ssl&auth_mode=login&...
```

Normalmente estará configurada en una variable de entorno.

```yml
swiftmailer:
    url: '%env(MAILER_URL)%'
    spool: { type: 'memory' }
```

```
MAILER_URL=smtp://user:pass@host:port/?timeout=60&encryption=ssl&auth_mode=login
```

La lista de opciones de configuración es la siguiente

- transport

  Los posibles valores son:
    - smtp
    - gmail
    - mail (obsoleto desde la versión 5.4.5)
    - sendmail
    - null (es lo mismo que poner disable_delivery a true)

- username

- password

- command

Comando que será ejecutado por sendmail. Por defecto */usr/sbin/sendmail -bs*

- host

- port

- timeout

Timeout en segundos cuando se utiliza smtp.

- source_ip

Únicamente válido en smpt

- local_domain

El nombre de dominio para utilizar en el comando *HELO*.

- encryption

Los posibles valores son *tls*, *ssl* o *null*.

- auth_mode

  Autenticación a utilizar para smtp. Los posibles valores son:

    - plain
    - login
    - cram-md5
    - null

- spool

  Para la configuración de gestión de envío de mensajes. Tiene dos subopciones: 

    - type: que puede valer *memory* o *file*
    - path: directorio de los archivos si el tipo de spool es file.

- sender_address

Si se establece esta opción, todos los mensajes tendrán esta dirección como la "dirección de respuesta".

- antiflood
  - threshold: número de emails enviados antes resetear el transporte.
  - sleep: número de segundos a esperar durante el reseteo del transporte.

- delivery_addresses

Si se establece un valor, TODOS los correos se enviarán a estas direcciones en lugar de enviarse a las direcciones originales. Es una opción útil durante el desarrollo.

- delivery_whitelist

Si se establece, los emails con direcciones incluidas en esta lista, serán enviados a dichas direcciones además de ser enviados a las direcciones indicadas en delivery_addresses.

- disable_delivery

Si se establece a *true*, no se enviarán correos.

- logging

Si se establece a *true*, el data collector asociado recogerá información de Swift Mailer y la mostrará en el profiler. Por defecto tiene el valor *%kernel.debug%*.

## Envío de emails

La librería de Swift Mailer trabjaja con la clases **Swift_Message** para construir un mensaje/correo y la clase/servicio **Swift_Mailer** para enviar los mensajes/correos. 

```php
public function index($name, \Swift_Mailer $mailer)
{
    $message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody(
            $this->renderView(
                // templates/emails/registration.html.twig
                'emails/registration.html.twig',
                array('name' => $name)
            ),
            'text/html'
        )
        /*
         * Se puede incluir una versión en texto plano del mensaje
        ->addPart(
            $this->renderView(
                'emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
    ;

    $mailer->send($message);

    return $this->render(...);
}
```

Para mantener los elementos desacoplados, el cuerpo del mensaje se puede programar en una plantilla de Tiwg y renderizarlo con la función **renderView()**.

El fichero de Twig registration.html.twig podría ser algo parecido a esto:

```html
{# templates/emails/registration.html.twig #}
<h3>¡Enhorabuena!</h3>

Hola {{ name }}. Te has registrado con éxito.

Para hacer login, ve a: <a href="{{ url('login') }}">Login</a>.

¡Gracias!

<img src="{{ absolute_url(asset('images/logo.png')) }}">
```

La clase Swift_Message soporta más opciones, como por ejemplo, adjuntar archivos. El resumen de los métodos de dicha clase es el se puede ver a continuación:

```php
$message = (new Swift_Message())

  // Asunto del mensaje
  ->setSubject('Your subject')

  // Dirección o direcciones de correo para el From
  ->setFrom(['john@doe.com' => 'John Doe'])

  // Direcciones de correo para el To, Cc y Bcc (setTo/setCc/setBcc)
  ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])

  // Cuerpo del mensaje
  ->setBody('Here is the message itself')

  // Cuerpo alternativo del mensaje
  ->addPart('<q>Here is the message itself</q>', 'text/html')

  // Archivos adjuntos
  ->attach(Swift_Attachment::fromPath('my-document.pdf'))
  ;
```

Más información en:
https://swiftmailer.symfony.com/docs/messages.html

## Cómo trabajar con emails durante el desarrollo

### Deshabilitar envío de correos

Podemos deshabilitar el envío de correos en el entorno de desarrollo con la opción **disable_delivery**.

```yml
# config/packages/test/swiftmailer.yaml
swiftmailer:
    disable_delivery: true
```

### Enviar todos los correos a la cuenta del desarrollador

La opción **delivery_addresses** nos permite enviar los correos a unas direcciones determinadas en lugar de a sus destinatarios originales.

De esta forma, durante el desarrollo, no bombardearemos a los usuarios con correos de testeo. Tampoco necesitaremos molestarles para preguntarles si les han llegado los correos.

```yml
swiftmailer:
    delivery_addresses: ['dev@example.com']
```

### Enviar todos los correos a la cuenta del desarrollador con excepciones

La opción **delivery_whitelist** permite configurar expresiones regulares. Si el destinatario de un correo cumple alguna de las expresiones regulares, se le entregará el correo, además de entregarse también a los correos configurados en *delivery_addresses*.

```yml
# config/packages/dev/swiftmailer.yaml
swiftmailer:
    delivery_addresses: ['dev@example.com']
    delivery_whitelist:
       # all email addresses matching these regexes will be delivered
       # like normal, as well as being sent to dev@example.com
       - '/@specialdomain\.com$/'
       - '/^admin@mydomain\.com$/'
```

La opción *delivery_whitelist* solamente funciona si está definida la opción *delivery_addresses*.

### Ver correos en la barra de depuración y en el profiler

En la barra de depuráción aparece un número indicando cuántos correos se han enviado. En el profiler se muestran los detalles de dichos correos.

Si enviamos un email y acto seguido redirigimos a otra página, en la barra de depuración no se mostrará el email enviado.

Tenemos la opción de buscar el profiler de la petición anterior, en el buscador del profiler, pero también podemos establecer la opción **intercept_redirects** a *true* en el entorno de *dev*. Esto provoca que la redirección se pare y se pueda ver en el profiler la información de los emails enviados.

```yml
# config/packages/dev/web_profiler.yaml
web_profiler:
    intercept_redirects: true
```

## Cómo configurar varios mailers

Configurar varios mailers es muy sencillo. Basta poner un nombre a cada mailer, y a partir del nombre la configuración de cada uno.

```yml
swiftmailer:
    default_mailer: primer_mailer
    mailers:
        primer_mailer:
            # ...
        segundo_mailer:
            # ...
```

Cada mailer es registrado automáticamente como un servicio con estos IDs:

```php
// ...

// devuelve el primer_mailer
$container->get('swiftmailer.mailer.primer_mailer');

// devuelve el mailer por defecto (primer_mailer)
$container->get('swiftmailer.mailer');

// devuelve el segundo_mailer
$container->get('swiftmailer.mailer.segundo_mailer');
```

Si estamos utilizando *autowiring*, el servicio que se inyectará será siempre el mailer que se haya configurado como *default_mailer*. Si necesitamos inyectar otro de los mailers, debemos configurarlo manualmente:

```yml
# config/services.yaml
services:
    _defaults:
        bind:
            # inyecta segundo_mailer en los argumentos de constructor tipados con \Swift_Mailer
            \Swift_Mailer: '@swiftmailer.mailer.segundo_mailer'
            # inyecta segundo_mailer en los argumentos de servicios cuyo nombre sea $specialMailer
            $specialMailer: '@swiftmailer.mailer.segundo_mailer'

    App\Some\Service:
        # inyecta segundo_mailer en el argumento $differentMailer del constructor del servicio App\Some\Service
        $differentMailer: '@swiftmailer.mailer.segundo_mailer'

    # ...
```

## How to Spool Emails

El comportamiento predeterminado del mailer de Symfony es enviar los mensajes de correo electrónico inmediatamente. Sin embargo, el envío de correo no es eficiente (consume bastante tiempo). 

Para evitar que el usuario espere mientras se envía el correo electrónico a que se cargue la página siguiente, se pueden configura "poner en cola" los correos electrónicos en lugar de enviarlos directamente.

Esto hace que el mailer no intente enviar el mensaje de correo electrónico, sino que lo guarde en algún lugar, como un archivo. Otro proceso puede luego leer desde el *spool* y encargarse de enviar los correos electrónicos pendientes. Actualmente solo se admite spool en archivo o en memoria.

### Spool usando memoria

Con esta configuración, los correos se almacenan en memoria y serán enviados justo antes de que el Kernel de Symfony termine su ejecución.

Esto significa que el correo electrónico solo se envía si la solicitud completa se ejecutó sin ninguna excepción no manejada o cualquier error. 

```yml
# config/packages/swiftmailer.yaml
swiftmailer:
    # ...
    spool: { type: memory }
```

### Spool usando ficheros

Con esta configuración, Symfony crea una carpeta en la ruta dada para cada servicio de correo (por ejemplo, "default" para el servicio mailer predeterminado).

Esta carpeta contendrá un archivo por cada correo electrónico en el spool. Hay que asegurarse por lo tanto que el directorio es escribible por Symfony.

La configuración sería la siguiente:

```yml
# config/packages/swiftmailer.yaml
swiftmailer:
    # ...
    spool:
        type: file
        path: /path/to/spooldir
        # path: '%kernel.project_dir%/var/spool'
```

Con esta configuración, cuando la aplicación envíe un email realmente no se enviará el email, sino que se almacenará diciho en el spool.

El envío de emails debe realizarse a través de la consola de comandos.

> php bin/console swiftmailer:spool:send

Hay una opción para limitar el número de mensajes a enviar:

> php bin/console swiftmailer:spool:send --env=prod --message-limit=10

Y otra opción para limitar el tiempo:

> php bin/console swiftmailer:spool:send --env=prod --time-limit=10

Normalmente esta tarea no se lanzará a mano sino que estará programada en un cron o similar.

PRECAUCIÓN:

Al crear un mensaje con SwiftMailer, se genera una clase Swift_Message. Si el servicio swiftmailer se carga de forma lazy, genera realmente un proxy llamado Swift_Message_xxxxxxxx.

Con spool de memoria, esto es transparente. Pero con spool de ficheros, la clase es serializada en un archivo con su nombre de clase. Este nombre de clase **cambia cada vez que se limpia caché**. Por lo que después de limpiar caché, los mensajes no se podrán des-serializar y el comando swiftmailer:spool:send generará un error dado que la clase serializada no existe.

Las soluciones son: o bien utilizar el spool de memoria, o bien cargar el servicio sin la opción lazy.

https://symfony.com/doc/current/service_container/lazy_services.html

## Inyectar Twig en un servicio

Si queremos enviar un correo desde un servicio, y queremos utilizar una plantilla de twig en ese correo, necesitamos el servicio de Twig:

```php
public function __construct(..... \Twig_Environment $twig .....)
{
    .......
    $this->twig = $twig;
}
```

así tenemos disponible $this->twig->render(....)