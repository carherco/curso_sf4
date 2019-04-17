# Doctrine

## Doctrine ORM

El framework symfony no incluye ningún componente para trabajar con bases de datos.
Sin embargo proporciona integración con una librería llamada Doctrine.

NOTA:
Doctrine ORM está totalmente desacoplado de Symfony y no es obligatorio utilizarlo.

Permite mapear objetos a una base de datos relacional (como MySQL, PostgreSQL o Microsoft SQL).

Si lo que se quiere es ejecutar consultas en bruto a la base de datos, se puede utilizar Doctrine DBAL.

https://symfony.com/doc/current/doctrine/dbal.html

También se pueden utilizar bases de datos no relacionales como MongoDB.

http://symfony.com/doc/master/bundles/DoctrineMongoDBBundle/index.html

Para utilizar Doctrine hay que isntalarlo, junto con el bundle MakerBundle, que tiene las utilidades para generar código:

> composer require doctrine

> composer require maker --dev

### Configurar el acceso a la base de datos

La información de conextión de base de datos está almacenada en una variable de entorno llamada **DATABASE_URL**:

```yml
# .env

# customize this line!
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

# to use sqlite:
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"
```

### Crear la base de datos

Podemos crear la base de datos a mano, o utilizar la consola de symfony:

> bin/console doctrine:database:create

### Crear las entidades

Aquí igual: podemos crear a mano las entidades, o utilizar la consola de symfony.
Una entidad no es más que una clase decorada con decoradores de Doctrine.

> bin/console make:entity

Las entidades se guardan generalmente en el directorio *src/Entity*.

Podéis ver una lista de todos los comandos utilizando el siguiente comando:

> php bin/console list doctrine

### Mapeo básico de entidades

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#basic-mapping

### Validar las entidades

Otro comando interesante es *doctrine:schema:validate* que valida las entidades.

> bin/console doctrine:schema:validate

## Operaciones de INSERT, SELECT, UPDATE y DELETE

### Persistir objetos en la base de datos (INSERT)

Utilizamos el objeto EntityManager para persistir objetos en la base de datos.

```php
public function createAction()
{
    // Podemos obtener el EntityManager a través de $this->getDoctrine()
    // o con inyección de dependencias: createAction(EntityManagerInterface $em)
    $em = $this->getDoctrine()->getManager();

    $grado = new Grado();
    $grado->setNombre('Ingeniería de montes');

    // Informamos a Doctrine de que queremos guardar el Grado (todavía no se ejecuta ninguna query)
    $em->persist($grado);

    // Para ejecutar las queries pendientes, se utiliza flush().
    $em->flush();

    return new Response('El id del nuevo grado creado es '.$grado->getId());

}
```

Si tenemos definidas varias conexiones, podemos instanciar un objeto EntityManager para cada una de las conexiones.

```php
$doctrine = $this->getDoctrine();
$em = $doctrine->getManager();
$em2 = $doctrine->getManager('other_connection');
```

Configurar varias conexiones es muy sencillo:

```yml
# app/config/config.yml
doctrine:
    dbal:
        default_connection:   default
        connections:
            # A collection of different named connections (e.g. default, conn2, etc)
            default:
                dbname:               bd1
                host:                 10.0.1.6
                port:                 ~
                user:                 root
                password:             ~
                charset:              ~
                path:                 ~
                memory:               ~
            other_connection:
                dbname:               bd2
                host:                 10.0.1.7
                port:                 ~
                user:                 root
                password:             ~
                charset:              ~
                path:                 ~
                memory:               ~
```

### Recuperar objetos de la base de datos (SELECT)

```php
public function showAction($id)
{
    $grado = $this->getDoctrine()
        ->getRepository(Grado::class)
        ->find($id);

    if (!$grado) {
        throw $this->createNotFoundException(
            'No se ha encontrado ningún grado con el id '.$id
        );
    }

}
```

### La barra de depuración

Podemos consultar en la barra de depuración todas las SQLs ejecutadas, así como el tiempo y la memoria consumidos con cada petición.

Haciendo click en el icono de la base de datos vemos toda la información detallada.

### Editar un objeto (UPDATE)

Editar un registro es igual de fácil que crearlo, simplemente en vez de hacer un 
new, partimos de un registro ya existente obtenido de la base de datos.

```php
public function showAction($id, EntityManagerInterface $em)
{
    $grado = $this->getDoctrine()->getRepository(Grado::class)->find($id);

    $grado->setNombre('otro nombre');

    $em->persist($grado);
    $em->flush();
    ...
}
```

### Eliminar un objeto (DELETE)

Para eliminar un registro utilizamos el método remove() del EntityManager.

```php
public function showAction($id, EntityManagerInterface $em)
{
    $grado = $this->getDoctrine()->getRepository(Grado::class)->find($id);

    $em->remove($grado);
    $em->flush();
    ...
}
```

## Relaciones entre entidades

https://symfony.com/doc/current/doctrine/associations.html

Las entidades Asignatura y Grado están relacionadas. Un asignatura pertenece a un grado y un grado tiene muchas asignaturas.

Desde la perspectiva de la entidad Asignatura, es una relación *many-to-one*. Desde la perspectiva de la entidad Grado, es una relación *one-to-many*.

La naturaleza de la relación determina qué metadatos de mapeo se van a utilizar. También determina qué entidad contendrá una referencia a la otra entidad

Para relacionar las entidades Asignatura y Grado, simplemente creamos una propiedad
*grado* en la entidad Asignatura con las anotaciones que vemos a continuación:

```php
class Asignatura
{
    // ...

    /**
     * @ORM\ManyToOne(targetEntity="Grado", inversedBy="asignaturas")
     * @ORM\JoinColumn(name="grado_id", referencedColumnName="id")
     */
    private $grado;
}
```

Hemos informado a Doctrine que utilice *grado_id* como columna en la tabla *asignatura* para relacionar el registro con la tabla *grado*.

Lo siguiente es indicar a Doctrine que una entidad Grado está relacionada con muchas entidades Asignatura a través de una propiedad asignaturas en la entidad Grado. 

```php
use Doctrine\Common\Collections\ArrayCollection;

class Grado
{
    // ...

    /**
     * @ORM\OneToMany(targetEntity="Asignatura", mappedBy="grado")
     */
    private $asignaturas;

    public function __construct()
    {
        $this->asignaturas = new ArrayCollection();
    }
}
```

La asociación many-to-one es obligatoria, pero la one-to-may es opcional.

El código en el constructor es importante. En lugar de ser instanciado como un array tradicional, la propiedad $asignaturas debe ser de un tipo que implemente la interface *DoctrineCollection*. El objeto ArrayCollection es de este tipo.

El objeto *ArrayCollection* parece y se comporta casi exactamente como un array por lo que todas las operaciones válidas sobre arrays, serán válidas sobre ArrayCollection.

Ya solamente queda crear los getters y setters correspondientes.

Si ahora actualizamos el schema, se generarán las relaciones en la base de datos

> bin/console doctrine:schema:update --force

### Guardando entidades relacionadas

```php
use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function createProductAction()
    {
        $category = new Category();
        $category->setName('Computer Peripherals');

        $product = new Product();
        $product->setName('Keyboard');
        $product->setPrice(19.99);
        $product->setDescription('Ergonomic and stylish!');

        // relate this product to the category
        $product->setCategory($category);

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->persist($product);
        $em->flush();

        return new Response(
            'Saved new product with id: '.$product->getId()
            .' and new category with id: '.$category->getId()
        );
    }
}
```

### Navegar entre entidades relacionadas

```php
$product = $this->getDoctrine()
        ->getRepository(Product::class)
        ->find($productId);

    $categoryName = $product->getCategory()->getName();
```

También tenemos un método get en la otra entidad.

```php
$category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->find($categoryId);

    $products = $category->getProducts();
```

### Mapeo de relaciones

Los tipos de asociaciones que ofrece Doctrine son los siguientes:

- @ManyToOne (unidireccional)
- @OneToOne (unidireccional / bidireccional / autorreferenciado)
- @OneToMany (bidirectional / unidireccional con join table / autorreferenciado)
- @ManyToMany (unidireccional / bidireccional / autorreferenciado)

Ejemplos:

- Many-To-One, Unidireccional

```php
<?php
/** @Entity */
class User
{
    // ...

    /**
     * @ManyToOne(targetEntity="Address")
     * @JoinColumn(name="address_id", referencedColumnName="id")
     */
    private $address;
}

/** @Entity */
class Address
{
    // ...
}
```

- One-To-One, Unidireccional

```php
<?php
/** @Entity */
class Product
{
    // ...

    /**
     * One Product has One Shipment.
     * @OneToOne(targetEntity="Shipment")
     * @JoinColumn(name="shipment_id", referencedColumnName="id")
     */
    private $shipment;

    // ...
}

/** @Entity */
class Shipment
{
    // ...
}
```

- One-To-One, Bidirectional

```php
/** @Entity */
class Customer
{
    // ...

    /**
     * One Customer has One Cart.
     * @OneToOne(targetEntity="Cart", mappedBy="customer")
     */
    private $cart;

    // ...
}

/** @Entity */
class Cart
{
    // ...

    /**
     * One Cart has One Customer.
     * @OneToOne(targetEntity="Customer", inversedBy="cart")
     * @JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    // ...
}
```

- One-To-One, Auto-referenciado

```php
/** @Entity */
class Student
{
    // ...

    /**
     * One Student has One Student.
     * @OneToOne(targetEntity="Student")
     * @JoinColumn(name="mentor_id", referencedColumnName="id")
     */
    private $mentor;

    // ...
}
```

- One-To-Many, Bidireccional

```php
<?php
use Doctrine\Common\Collections\ArrayCollection;

/** @Entity */
class Product
{
    // ...
    /**
     * One Product has Many Features.
     * @OneToMany(targetEntity="Feature", mappedBy="product")
     */
    private $features;
    // ...

    public function __construct() {
        $this->features = new ArrayCollection();
    }
}

/** @Entity */
class Feature
{
    // ...
    /**
     * Many Features have One Product.
     * @ManyToOne(targetEntity="Product", inversedBy="features")
     * @JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;
    // ...
}
```

- One-To-Many, Auto-referenciado

```php
<?php
/** @Entity */
class Category
{
    // ...
    /**
     * One Category has Many Categories.
     * @OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * Many Categories have One Category.
     * @ManyToOne(targetEntity="Category", inversedBy="children")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;
    // ...

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
}
```

- Many-To-Many, Unidireccional

```php
<?php
/** @Entity */
class User
{
    // ...

    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="Group")
     * @JoinTable(name="users_groups",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    private $groups;

    // ...

    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }
}

/** @Entity */
class Group
{
    // ...
}
```

- Many-To-Many, Bidireccional

```php
<?php
/** @Entity */
class User
{
    // ...

    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="Group", inversedBy="users")
     * @JoinTable(name="users_groups")
     */
    private $groups;

    public function __construct() {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // ...
}

/** @Entity */
class Group
{
    // ...
    /**
     * Many Groups have Many Users.
     * @ManyToMany(targetEntity="User", mappedBy="groups")
     */
    private $users;

    public function __construct() {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // ...
}
```

- Many-To-Many, Auto-referenciado

```php
<?php
/** @Entity */
class User
{
    // ...

    /**
     * Many Users have Many Users.
     * @ManyToMany(targetEntity="User", mappedBy="myFriends")
     */
    private $friendsWithMe;

    /**
     * Many Users have many Users.
     * @ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @JoinTable(name="friends",
     *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     */
    private $myFriends;

    public function __construct() {
        $this->friendsWithMe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myFriends = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // ...
}
```

## Mapping defaults

Las definiciones de @JoinColumn y de @JoinTable son opcionales y tienen como valores por defecto los siguientes:

```
name: "_id"
referencedColumnName: "id"
```

Por lo tanto, este mapeo 

```php
/** @OneToOne(targetEntity="Shipment") */
private $shipment;
```

es equivalente a este

```php
/**
 * One Product has One Shipment.
 * @OneToOne(targetEntity="Shipment")
 * @JoinColumn(name="shipment_id", referencedColumnName="id")
 */
private $shipment;
```

y este mapeo

```php
class User
{
    //...
    /** @ManyToMany(targetEntity="Group") */
    private $groups;
    //...
}
```

es equivalente a este

```php
class User
{
    //...
    /**
     * Many Users have Many Groups.
     * @ManyToMany(targetEntity="Group")
     * @JoinTable(name="User_Group",
     *      joinColumns={@JoinColumn(name="User_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="Group_id", referencedColumnName="id")}
     *      )
     */
    private $groups;
    //...
}
```

## Colecciones

Los arrays de PHP no son aptos para lazy loading. Es por esto, que en las asociaciones X-To-Many, se utilizan los tipos de datos **Collection** y **ArrayCollection**.

```php
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/** @Entity */
class User
{
    /**
     * Many Users have Many Groups.
     * @var Collection
     * @ManyToMany(targetEntity="Group")
     */
    private $groups;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
```

### Owning side and inverse side

Al asignar las asociaciones bidireccionales, es importante entender el concepto de **owning side** y de **inverse side**. Estas son las reglas generales:

- Las relaciones pueden ser bidireccionales o unidireccionales.
- Una relación bidireccional tiene un *owning side* y un *inverse side*.
- Una relación unidireccional solamente tiene *owning side*.
- Doctrine solamente comprueba si ha habido cambios en la entidad *owning side*.

Las siguientes reglas se aplican a bidireccionales asociaciones:

- El *inverse side* tiene que tener el atributo mappedBy en las OneToOne, OneToMany o ManyToMany.
- El *owning side* tiene que tener el atributo inversedBy en las OneToOne, ManyToOne o ManyToMany.
- ManyToOne es siempre el *owning side* de una asociación bidireccional.
- OneToMany es siempre el *inverse side* de una asociación bidireccional.
- El *owning side* de una OneToOne es la entidad con la tabla que contiene la clave externa.
- En una ManyToMany tenemos que decidir cuál es la owning y cuál la inverse.

Doctrine solamente comprueba la *owning side*. Los cambios realizados sólo en el *inverse side* se ignoran.

## Transacciones

### Transacción implícita o automática

```php
$user = new User;
$user->setName('George');
$em->persist($user);
$em->flush();
```

### Transacción explícita o manual

```php
$em->getConnection()->beginTransaction(); // suspend auto-commit
try {
    //... do some work
    $user = new User;
    $user->setName('George');
    $em->persist($user);
    $em->flush();
    $em->getConnection()->commit();
} catch (Exception $e) {
    $em->getConnection()->rollBack();
    throw $e;
}
```

O bien de esta forma equivalente:

```php
<?php
$em->transactional(function($em) {
    //... do some work
    $user = new User;
    $user->setName('George');
    $em->persist($user);
});
```

## Cascade

Podemos configurar modificaciones en cascada a dos niveles: nivel doctrine o nivel base de datos.

A nivel de doctrine

```php
/**
 * @OneToMany(targetEntity="Phonenumber", mappedBy="user", cascade={"persist", "remove", "merge", "refresh"}, orphanRemoval=true)
 */
public $phonenumbers;
```

A nivel de base de datos en @JoinColumn

```php
/**
 * @OneToOne(targetEntity="Customer")
 * @JoinColumn(name="customer_id", referencedColumnName="id", onDelete="cascade", onUpdate="cascade")
 */
private $customer;
```

## Extensiones de doctrine

Para ciertas estructuras y comportamientos habituales de tablas, hay desarrolladores que programan extensiones de doctrine.

https://symfony.com/doc/current/doctrine/common_extensions.html

https://github.com/Atlantic18/DoctrineExtensions

## Documentación general

https://symfony.com/doc/current/doctrine.html