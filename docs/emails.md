# Envío de Correos

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

### Enviar todos los correos a la cuenta del desarrollador con excepciones

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

## Cómo configurar varios mailers

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