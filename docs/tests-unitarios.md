Tests Unitarios
===============

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


