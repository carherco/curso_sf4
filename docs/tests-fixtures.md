Fixtures
========


> composer require --dev doctrine/doctrine-fixtures-bundle

Los fixtures son clases PHP en donde creamos objetos y los persistimos en la base de datos. Estas clases deben extender de **Doctrine\Bundle\FixturesBundle\Fixture** y deben implementar el método **load()**.

El siguiente ejemplo muestra un fixtures que crea 20 productos y los persiste:

```php
// src/DataFixtures/AppFixtures.php
namespace App\DataFixtures;

use App\Entity\Producto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Creamos y persistimos 20 productos
        for ($i = 0; $i < 20; $i++) {
            $product = new Producto();
            $product->setNombre('product '.$i);
            $product->setPrecio(mt_rand(10, 100));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
```

El comando para cargar la base de datos con fixtures es:

> bin/console doctrine:fixtures:load

Este comando, primero purga la base de datos. Si queremos añadir los fixtures sin borrar previamente la base de datos, podemos añadir la opción *--append* al comando.

> bin/console doctrine:fixtures:load --append


Dividir los fixtures en ficheros separados
------------------------------------------

En ocasiones, la clase de fixtures crece demasiado y decidimos separarla en 2 o más clases. Symfony soluciona los dos asuntos más comunes que se dan en estos casos: compartir objetos entre las clases fixture y cargar los fixtures en orden.



Compartir objetos entre Fixtures
--------------------------------

Symfony nos proporciona dos métodos **addReference()** y **getReference()** para compartir objetos entre fixtures.


```php
// src/DataFixtures/UserFixtures.php
// ...
class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function load(ObjectManager $manager)
    {
        $userAdmin = new User('admin', 'pass_1234');
        $manager->persist($userAdmin);
        $manager->flush();

        // other fixtures can get this object using the UserFixtures::ADMIN_USER_REFERENCE constant
        $this->addReference(self::ADMIN_USER_REFERENCE, $userAdmin);
    }
}
```

```php
// src/DataFixtures/GroupFixtures.php
// ...
class GroupFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $userGroup = new Group('administrators');
        // this reference returns the User object created in UserFixtures
        $userGroup->addUser($this->getReference(UserFixtures::ADMIN_USER_REFERENCE));

        $manager->persist($userGroup);
        $manager->flush();
    }
}
```

Lo único que queda por solucionar es que estas clases deben ejecutarse en un orden determinado.


Cargar los ficheros de fixtures en orden
----------------------------------------

Una clase fixture que dependa de otra, debe implementar el interfaz  **DependentFixtureInterface** y añadir un método **getDependencies()** en el que se indican las clases de las que depende.

```php
// src/DataFixtures/UserFixtures.php
namespace App\DataFixtures;

// ...
class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // ...
    }
}
```

```php
// src/DataFixtures/GroupFixtures.php
namespace App\DataFixtures;
// ...
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // ...
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
```

Symfony ejecutará las clases en un orden que respete las dependencias.

