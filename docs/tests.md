# Testeo de aplicaciones

Symfony se integra con la librería independiente PHPUnit para proporcionar un potente framework de testeo.

Lo primero de todo es intalar el componente **PHPUnit Bridge** que añade funcionalidades adicionales a las propias de PHPUnit.

> composer require --dev symfony/phpunit-bridge

Cada test -no importa si es funcional o unitario- debe estar situado en el directorio */tests/* de la aplicación.

Si seguimos esta sencilla regla, todos nuestros tests se ejecutarán con el siguiente comando:

> ./vendor/bin/simple-phpunit

La configuración de PHPUnit se encuentra en el archivo phpunit.xml.dist de nuestro proyecto.

Por ejemplo, se puede configurar una base de datos diferente para testeo:

```xml
<?xml version="1.0" charset="utf-8" ?>
<phpunit>
    <php>
        <!-- the value is the Doctrine connection string in DSN format -->
        <env name="DATABASE_URL" value="mysql://USERNAME:PASSWORD@127.0.0.1/DB_NAME" />
    </php>
    <!-- ... -->
</phpunit>
```

## PHP Unit

Los tests con PHP unit tienen que cumplir ciertas normas.

- Los tests son son métodos públicos de una clase denominada ClassTest.
- ClassTest hereda (casi siempre) de PHPUnit\Framework\TestCase.
- Los nombres de los tests deben empezar por test*.

Como alternativa, puedes utilizar la anotación @test en el docblock de un método para marcarlo como un test.

- Dentro de cada función de test se utilizan funciones de *aserción* para verificar que obtenemos los resultados esperados.

### Ejecución de los tests

En symfony, el comando para ejecutar los tests no es de la consola de comandos. Es un comando de la propia librería de phpunit.

```
# Ejecuta todos los tests
./vendor/bin/simple-phpunit

# Ejecuta los tests del directorio Util
./vendor/bin/simple-phpunit tests/Util

# Ejecuta los tests de la clase Util/CalculatorTest.php
./vendor/bin/simple-phpunit tests/Util/CalculatorTest.php
```

### Assertions

La lista completa de métodos de aserción es la siguiente:

- assertArrayHasKey()
- assertClassHasAttribute()
- assertArraySubset()
- assertClassHasStaticAttribute()
- assertContains()
- assertContainsOnly()
- assertContainsOnlyInstancesOf()
- assertCount()
- assertDirectoryExists()
- assertDirectoryIsReadable()
- assertDirectoryIsWritable()
- assertEmpty()
- assertEqualXMLStructure()
- assertEquals()
- assertFalse()
- assertFileEquals()
- assertFileExists()
- assertFileIsReadable()
- assertFileIsWritable()
- assertGreaterThan()
- assertGreaterThanOrEqual()
- assertInfinite()
- assertInstanceOf()
- assertInternalType()
- assertIsReadable()
- assertIsWritable()
- assertJsonFileEqualsJsonFile()
- assertJsonStringEqualsJsonFile()
- assertJsonStringEqualsJsonString()
- assertLessThan()
- assertLessThanOrEqual()
- assertNan()
- assertNull()
- assertObjectHasAttribute()
- assertRegExp()
- assertStringMatchesFormat()
- assertStringMatchesFormatFile()
- assertSame()
- assertStringEndsWith()
- assertStringEqualsFile()
- assertStringStartsWith()
- assertThat()
- assertTrue()
- assertXmlFileEqualsXmlFile()
- assertXmlStringEqualsXmlFile()
- assertXmlStringEqualsXmlString()

https://phpunit.readthedocs.io/en/7.4/assertions.html#

- Un test debe tener al menos un assert.
- Para que un test se considere superado, deben cumplirse todos sus asserts.

### Estructura recomendada de un test

Un test normalmente tiene 3 partes bien diferenciadas

- **Setup**: El setup
- **Act**: La ejecución del código que se quiere testear
- **Assert**: La comprobación

```php
public function testTransferencia()
    {
        // Set up
        $cuenta1 = new Cuenta();
        $cuenta1->ingreso(500);

        $cuenta2 = new Cuenta();
        $cuenta2->ingreso(50);

        // Act
        $cuenta1->transferencia($cuenta2,100);

        // Assert
        $this->assertEquals(400, $cuenta1->getSaldo());
        $this->assertEquals(150, $cuenta2->getSaldo());
    }
```

### Características que deben cumplir los buenos tests

- Cada test debe testear una única funcionalidad.
- Ningún test debe depender de que otro test se haya ejecutado antes.

### Los métodos setup() y teardown()

- El método **setup()** es un método especial que se ejecuta **antes de cada test**.
- El método **teardown()** es un método especial que se ejecuta **después de cada test**.

El método setup() Se suelen utilizar cuando todos los tests de la clase tienen parte del setup en común. El método teardown() se suele utilizar para deshacer los posibles cambios que los tests hayan podido hacer en el entorno global y/o para liberar recursos.

```php
// tests/Repository/ProductRepositoryTest.php
namespace App\Tests\Repository;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testSearchByCategoryName()
    {
        $products = $this->entityManager
            ->getRepository(Product::class)
            ->searchByCategoryName('foo')
        ;

        $this->assertCount(1, $products);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
```

### Los métodos setUpBeforeClass() and tearDownAfterClass()

- El método **setUpBeforeClass()** se ejecuta una única vez antes del primer test.
- El método **tearDownAfterClass()** se ejecuta una única vez después del último test.

### Tests incompletos

En el caso de que sepamos que tenemos que hacer un test, pero todavía no lo hayamos hecho por la razón que sea, dejar el método en blanco no es buena solución

```php
public function testSomething()
{
}
```

Al ejecutar phpunit, se marcará como bueno, y se nos puede olvidar que tenemos pendiente completarlo.

Podemos hacer que falle a propósito con $this->fail()

```php
public function testSomething()
{
  $this->fail();
}
```

Pero tampoco es buena idea. Hará que el test falle y que nos lleve a pensar de que nuestro código tiene bugs y no se puede utilizar en producción.

PhpUnit proporciona una forma de marcarlo como **incompleto**.

```php
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase
{
    public function testSomething()
    {
        // Optional: Test anything here, if you want.
        $this->assertTrue(true, 'This should already work.');

        // Stop here and mark this test as incomplete.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
```

La salida del testeo será la siguiete

```
$ phpunit --verbose SampleTest
PHPUnit |version|.0 by Sebastian Bergmann and contributors.

I

Time: 0 seconds, Memory: 3.95Mb

There was 1 incomplete test:

1) SampleTest::testSomething
This test has not been implemented yet.

/home/sb/SampleTest.php:12
OK, but incomplete or skipped tests!
Tests: 1, Assertions: 1, Incomplete: 1.
```

### Skipped tests

```php
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
              'The MySQLi extension is not available.'
            );
        }
    }

    public function testConnection()
    {
        // ...
    }
}
```

La salida será

```
$ phpunit --verbose DatabaseTest
PHPUnit |version|.0 by Sebastian Bergmann and contributors.

S

Time: 0 seconds, Memory: 3.95Mb

There was 1 skipped test:

1) DatabaseTest::testConnection
The MySQLi extension is not available.

/home/sb/DatabaseTest.php:9
OK, but incomplete or skipped tests!
Tests: 1, Assertions: 0, Skipped: 1.
```

Se pueden utilizar anotaciones para ignorar tests

```php
use PHPUnit\Framework\TestCase;

/**
 * @requires extension mysqli
 */
class DatabaseTest extends TestCase
{
    /**
     * @requires PHP 5.3
     */
    public function testConnection()
    {
        // Test requires the mysqli extension and PHP >= 5.3
    }

    // ... All other tests require the mysqli extension
}
```

- @requires PHP 5.3.3
- @requires PHP 7.1-dev
- @requires PHPUnit 3.6.3
- @requires PHPUnit 4.6
- @requires OS Linux
- @requires OS WIN32|WINNT
- @requires OSFAMILY Windows
- @requires function imap_open
- @requires function ReflectionMethod::setAccessible
- @requires extension mysqli
- @requires extension redis 2.2.0

### Dependencias

Se pueden marcar dependencias de unos tests con otros

```php
use PHPUnit\Framework\TestCase;

class DependencyFailureTest extends TestCase
{
    public function testOne()
    {
        $this->assertTrue(false);
    }

    /**
     * @depends testOne
     */
    public function testTwo()
    {
    }
}
```

```
$ phpunit --verbose DependencyFailureTest
PHPUnit |version|.0 by Sebastian Bergmann and contributors.

FS

Time: 0 seconds, Memory: 5.00Mb

There was 1 failure:

1) DependencyFailureTest::testOne
Failed asserting that false is true.

/home/sb/DependencyFailureTest.php:6

There was 1 skipped test:

1) DependencyFailureTest::testTwo
This test depends on "DependencyFailureTest::testOne" to pass.

FAILURES!
Tests: 1, Assertions: 1, Failures: 1, Skipped: 1.
```

### Proveedores de datos

```php
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @dataProvider additionProvider
     */
    public function testAdd($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
    }

    public function additionProvider()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 3]
        ];
    }
}
```

```
$ phpunit DataTest
PHPUnit |version|.0 by Sebastian Bergmann and contributors.

...F

Time: 0 seconds, Memory: 5.75Mb

There was 1 failure:

1) DataTest::testAdd with data set #3 (1, 1, 3)
Failed asserting that 2 is identical to 3.

/home/sb/DataTest.php:9

FAILURES!
Tests: 4, Assertions: 4, Failures: 1.
```

Proveedores con identificadores

```php
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @dataProvider additionProvider
     */
    public function testAdd($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
    }

    public function additionProvider()
    {
        return [
            'adding zeros'  => [0, 0, 0],
            'zero plus one' => [0, 1, 1],
            'one plus zero' => [1, 0, 1],
            'one plus one'  => [1, 1, 3]
        ];
    }
}
```

Múltiples proveedores

```php
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @dataProvider additionWithNonNegativeNumbersProvider
     * @dataProvider additionWithNegativeNumbersProvider
     */
    public function testAdd($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
    }

    public function additionWithNonNegativeNumbersProvider()
    {
        return [
            [0, 1, 1],
            [1, 0, 1],
            [1, 1, 3]
        ];
    }

    public function additionWithNegativeNumbersProvider()
    {
        return [
            [-1, 1, 0],
            [-1, -1, -2],
            [1, -1, 0]
        ];
    }
 }
```

### Testeo de excepciones

```php
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testException()
    {
        $this->expectException(XXXXXXException::class);
    }
}
```

### Testeo con dobles (Stubs, Mocks...)

Para crear stubs nos apoyamos en el método createMock() y en los métodos willReturn y will

Ejemplo con willReturn

```php
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    public function testStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(SomeClass::class);

        // Configure the stub.
        $stub->method('doSomething')
             ->willReturn('foo');

        // Calling $stub->doSomething() will now return
        // 'foo'.
        $this->assertSame('foo', $stub->doSomething());
    }
}
```

Ejemplo con will

```php
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    public function testReturnValueMapStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(SomeClass::class);

        // Create a map of arguments to return values.
        $map = [
            ['a', 'b', 'c', 'd'],
            ['e', 'f', 'g', 'h']
        ];

        // Configure the stub.
        $stub->method('doSomething')
             ->will($this->returnValueMap($map));

        // $stub->doSomething() returns different values depending on
        // the provided arguments.
        $this->assertSame('d', $stub->doSomething('a', 'b', 'c'));
        $this->assertSame('h', $stub->doSomething('e', 'f', 'g'));
    }
}
```

- will($this->returnValue($value))
- will($this->returnArgument(int)) // Indicamos qué argumento hay que retornar
- will($this->returnSelf())
- will($this->returnValueMap($map))

```php
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    public function testReturnValueMapStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(SomeClass::class);

        // Create a map of arguments to return values.
        $map = [
            ['a', 'b', 'c', 'd'],
            ['e', 'f', 'g', 'h']
        ];

        // Configure the stub.
        $stub->method('doSomething')
             ->will($this->returnValueMap($map));

        // $stub->doSomething() returns different values depending on
        // the provided arguments.
        $this->assertSame('d', $stub->doSomething('a', 'b', 'c'));
        $this->assertSame('h', $stub->doSomething('e', 'f', 'g'));
    }
}
```

- will($this->returnCallback('nombre_de_funcion'))
- will($this->$this->onConsecutiveCalls($val1, $val2, $val3, ...))

```php
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    public function testOnConsecutiveCallsStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(SomeClass::class);

        // Configure the stub.
        $stub->method('doSomething')
             ->will($this->onConsecutiveCalls(2, 3, 5, 7));

        // $stub->doSomething() returns a different value each time
        $this->assertSame(2, $stub->doSomething());
        $this->assertSame(3, $stub->doSomething());
        $this->assertSame(5, $stub->doSomething());
        $this->assertSame(7, $stub->doSomething());
    }
}
```

- will($this->throwException($exception))

```php
use PHPUnit\Framework\TestCase;

class StubTest extends TestCase
{
    public function testThrowExceptionStub()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->createMock(SomeClass::class);

        // Configure the stub.
        $stub->method('doSomething')
             ->will($this->throwException(new Exception));

        // $stub->doSomething() throws Exception
        $stub->doSomething();
    }
}
```

#### Mocks de WebServices

En estos casos, en vez del método createMock() tenemos el método getMockFromWsdl().

```php
use PHPUnit\Framework\TestCase;

class GoogleTest extends TestCase
{
    public function testSearch()
    {
        $googleSearch = $this->getMockFromWsdl(
          'url.wsdl', 'url'
        );

        $directoryCategory = new stdClass;
        $directoryCategory->fullViewableName = '';
        $directoryCategory->specialEncoding = '';

        $element = new stdClass;
        $element->summary = '';
        $element->URL = 'https://phpunit.de/';
        $element->snippet = '...';
        $element->title = '<b>PHPUnit</b>';
        $element->cachedSize = '11k';
        $element->relatedInformationPresent = true;
        $element->hostName = 'phpunit.de';
        $element->directoryCategory = $directoryCategory;
        $element->directoryTitle = '';

        $result = new stdClass;
        $result->documentFiltering = false;
        $result->searchComments = '';
        $result->estimatedTotalResultsCount = 37900;
        $result->estimateIsExact = false;
        $result->resultElements = [$element];
        $result->searchQuery = 'PHPUnit';
        $result->startIndex = 1;
        $result->endIndex = 1;
        $result->searchTips = '';
        $result->directoryCategories = [];
        $result->searchTime = 0.248822;

        $googleSearch->expects($this->any())
                     ->method('doGoogleSearch')
                     ->will($this->returnValue($result));

        /**
         * $googleSearch->doGoogleSearch() will now return a stubbed result and
         * the web service's doGoogleSearch() method will not be invoked.
         */
        $this->assertEquals(
          $result,
          $googleSearch->doGoogleSearch( 'PHPUnit' )
        );
    }
}
```

## Cobertura de código

PhpUnit es capaz de generar un informe de la cobertura de código de nuestros tests

> ./vendor/bin/simple-phpunit --coverage-html


## Enlaces interesantes

- https://matthiasnoback.nl/2014/07/test-doubles/
- https://symfony.com/doc/current/testing/database.html
- https://github.com/Codeception/Codeception/issues/3391
- https://gianarb.it/blog/symfony-unit-test-controller-with-phpunit
- https://symfony.com/blog/new-in-symfony-2-8-clock-mocking-and-time-sensitive-tests