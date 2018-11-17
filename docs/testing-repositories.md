Testeo de Repositorios de Doctrine
==================================

Para testear un repositorio de doctrine lo más sencillo es que nuestra clase de testeo extienda de **KernelTestCase**.

De esta forma, podemos acceder al objeto $kernel de forma muy sencilla con *self::bootKernel()* y a través del kernel obtener el *entityManager*, del cual podemos obtener el repositorio que queramos.

Veamos un ejemplo:


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


