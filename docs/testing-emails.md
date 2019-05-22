# Cómo testear si un email ha sido enviado

Se puede testear que un email ha sido enviado y comprobar cualquiera de sus propiedades (asunto, cuerpo, destinatario...) a través del profiler.

Veámoslo con un ejemplo.

Tenemos un controlador muy sencillo que simplemente envía un email.

```php
public function sendEmail($name, \Swift_Mailer $mailer)
{
    $message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody('You should see me from the profiler!')
    ;

    $mailer->send($message);

    return $this->render(...);
}
```

Lo podríamos testear de la siguiente forma:

```php
// tests/Controller/MailControllerTest.php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MailControllerTest extends WebTestCase
{
    public function testMailIsSentAndContentIsOk()
    {
        $client = static::createClient();

        // Habilita el profiler para la petición que vamos a realizar.
        $client->enableProfiler();

        $crawler = $client->request('POST', '/path/to/above/action');

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Comprueba si el email fue enviado
        $this->assertSame(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Más comprobaciones
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertSame('Hello Email', $message->getSubject());
        $this->assertSame('send@example.com', key($message->getFrom()));
        $this->assertSame('recipient@example.com', key($message->getTo()));
        $this->assertSame(
            'You should see me from the profiler!',
            $message->getBody()
        );
    }
}
```

## Notas

Al testear correos debemos prestar atención a dos cosas:

- Que el profiler esté habilitado
- No seguir la redirección en caso de que hubiera alguna
