# Tests Unitarios

Los tests unitarios no tienen nada de especial en symfony.

Veámoslo con un ejemplo que testea el método *add()* de una supuesta clase *App\Util\Calculator*:


```php
// tests/Util/CalculatorTest.php
namespace App\Tests\Util;

use App\Util\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testAdd()
    {
        $calculator = new Calculator();
        $result = $calculator->add(30, 12);

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }
}
```

Nuestra clase de test es una clase situada dentro del directorio **tests** y que extiende de la clase **TestCase**.

TestCase no es más que la clase de PHPUnit que contiene todos los métodos/assertions.

## Mock Services

Para crear Mocks de servicios, utilizaremos la función createMock().

```php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
// ...

public function testControllerResponse()
{
    $matcher = $this->createMock(Routing\Matcher\UrlMatcherInterface::class);
    // use getMock() on PHPUnit 5.3 or below
    // $matcher = $this->getMock(Routing\Matcher\UrlMatcherInterface::class);

    $matcher
        ->expects($this->once())
        ->method('match')
        ->will($this->returnValue([
            '_route' => 'foo',
            'name' => 'Fabien',
            '_controller' => function ($name) {
                return new Response('Hello '.$name);
            }
        ]))
    ;
    $matcher
        ->expects($this->once())
        ->method('getContext')
        ->will($this->returnValue($this->createMock(Routing\RequestContext::class)))
    ;
    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    $framework = new Framework($matcher, $controllerResolver, $argumentResolver);

    $response = $framework->handle(new Request());

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertContains('Hello Fabien', $response->getContent());
}
```