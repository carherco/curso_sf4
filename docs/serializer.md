# Introducción

El componente Serializer se utiliza para convertir objetos en cadenas de texto con formato específico (JSON, YAML, XML... ) y viceversa.

![Diagrama de flujo del Serializer](./img/serializer_workflow.png "Imagen extraída de symfony.com")

Para instalar el componente con Flex:

> composer require symfony/serializer

## Uso básico del Serializer

Para utilizar el Serializador, hay que configurarlo con los normalizadores y codificadores que queramos que soporte:

```php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

$encoders = array(new XmlEncoder(), new JsonEncoder());
$normalizers = array(new ObjectNormalizer());

$serializer = new Serializer($normalizers, $encoders);
```

### Serializar un objeto

```php
$person = new App\Entity\Producto();
$person->setNombre('nombre del producto');
$person->setPrecio(99);

$jsonContent = $serializer->serialize($person, 'json');

// $jsonContent contiene {"nombre":"nombre del producto","precio":99}
```

### Desserializar un objeto

```php
use App\Model\Person;

$data = <<<EOF
<person>
    <name>foo</name>
    <age>99</age>
    <sportsperson>false</sportsperson>
</person>
EOF;

$person = $serializer->deserialize($data, Person::class, 'xml');
```

Si hay atributos que no son de la clase, el comportamiento por defecto es ignorarlos. Pero se puede configurar el método deserialize para que lance un error en caso de encontrar atributos desconocidos: 

```php
$data = <<<EOF
<person>
    <name>foo</name>
    <age>99</age>
    <city>Paris</city>
</person>
EOF;

// this will throw a Symfony\Component\Serializer\Exception\ExtraAttributesException
// because "city" is not an attribute of the Person class
$person = $serializer->deserialize($data, 'Acme\Person', 'xml', array(
    'allow_extra_attributes' => false,
));
```

Se puede utilizar el serializar para actualizar un objeto existente:

```php
$person = new Person();
$person->setName('bar');
$person->setAge(99);
$person->setSportsperson(true);

$data = <<<EOF
<person>
    <name>foo</name>
    <age>69</age>
</person>
EOF;

$serializer->deserialize($data, Person::class, 'xml', array('object_to_populate' => $person));
// $person = App\Model\Person(name: 'foo', age: '69', sportsperson: true)
```

Si estamos des-serializando un array de objetos, entonces se le indica al método deserialize con []

```php
$persons = $serializer->deserialize($data, 'Acme\Person[]', 'json');
```

## Handling Circular References

Pongamos un ejemplo de una referencia circular entre 2 objetos:

```php
class Organization
{
    private $name;
    private $members;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setMembers(array $members)
    {
        $this->members = $members;
    }

    public function getMembers()
    {
        return $this->members;
    }
}

class Member
{
    private $name;
    private $organization;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function getOrganization()
    {
        return $this->organization;
    }
}
```

Para evitar un bucle infinito, los normalizadores,  GetSetMethodNormalizer y ObjectNormalizer lanzan una Excepción CircularReferenceException cuando se encuentran con referencias circulares.

```php
$member = new Member();
$member->setName('Kévin');

$organization = new Organization();
$organization->setName('Les-Tilleuls.coop');
$organization->setMembers(array($member));

$member->setOrganization($organization);

echo $serializer->serialize($organization, 'json'); // Lanza una CircularReferenceException
```

El método **setCircularReferenceLimit()** de estos normalizadores establecen el máximo número de veces que se va a serializar un mismo objeto antes de considerarlo una referencia circular. Por defecto este valor es de 1.

En vez de lanzar una excepción, se pueden manejar con un *callable* para ejecutar el código que queramos.

En el siguiente objeto, se "cambia" el objeto que provoca la referencia circular por un string que lo representa, de forma que se corta el bucle infinito.

```php
$encoder = new JsonEncoder();
$normalizer = new ObjectNormalizer();

$normalizer->setCircularReferenceHandler(function ($object) {
    return $object->getName();
});

$serializer = new Serializer(array($normalizer), array($encoder));
```

## Handling Serialization Depth

El componente Serializer es capaz de detectar y limitar la profundidad de la serialización.

Vamos a verlo con un ejemplo:

```php
namespace Acme;

class MyObj
{
    public $foo;

    /**
     * @var self
     */
    public $child;
}

$level1 = new MyObj();
$level1->foo = 'level1';

$level2 = new MyObj();
$level2->foo = 'level2';
$level1->child = $level2;

$level3 = new MyObj();
$level3->foo = 'level3';
$level2->child = $level3;
```

El serializador se puede configurar para establecer una profundidad máxima para una propiedad concreta.


```php
use Symfony\Component\Serializer\Annotation\MaxDepth;

namespace Acme;

class MyObj
{
    /**
     * @MaxDepth(2)
     */
    public $child;

    // ...
}
```

Para que el serializador tenga en cuenta la limitación de profundidad, hay que indicárselo con la clave **enable_max_depth**.

```php
$result = $serializer->normalize($level1, null, array('enable_max_depth' => true));
/*
$result = array(
    'foo' => 'level1',
    'child' => array(
            'foo' => 'level2',
            'child' => array(
                    'child' => null,
                ),
        ),
);
*/
```

A partir de Symfony 4.1 se puede definir una función manejadora de esta situación con el método setMaxDepthHandler():

```php
$normalizer = new ObjectNormalizer($classMetadataFactory);
$normalizer->setMaxDepthHandler(function ($foo) {
    return '/foos/'.$foo->id;
});

$serializer = new Serializer(array($normalizer));
$result = $serializer->normalize($level1, null, array(ObjectNormalizer::ENABLE_MAX_DEPTH => true));
/*
$result = array[
    'id' => 1,
    'child' => [
        'id' => 2,
        'child' => '/foos/3',
    ]
];
```

## Normalizadores

En Symfony tenemos varios normalizadores disponibles:

- **ObjectNormalizer**

Este normalizador es capaz de acceder a las propiedades directamente y también a través de getters, setters, hassers, adders y removers.

Soporta la llamada al constructor durante la denormalización.

El renombrado de método a propiedad es como se ve en este ejemplo:

getFirstName() -> firstName

Es el normalizador más potente de los que vienen con Symfony.

- **GetSetMethodNormalizer**

Este normalizador lee el contenido de la clase llamando a los *getters* (métodos públicos que empiecen por *get*) y denormaliza llamando al constructor de la clase y a los *setters* (métodos públicos que empiecen por *set*).

El renombrado de método a propiedad es como se ve en este ejemplo:

getFirstName() -> firstName

- **PropertyNormalizer**

Este normalizador lee y escribe todas las propiedades públicas, privadas y protected de la propia clase y de sus clases padre.

- **JsonSerializableNormalizer**

Este normalizador trabaja con clases que implementan JsonSerializable.

- **DateTimeNormalizer**

Convierte objetos DateTimeInterface (por ejemplo: DateTime y DateTimeImmutable) en strings. Por defecto utiliza el formato RFC3339.

- **DataUriNormalizer**

Convierte objetos SplFileInfo en *data URI string* (data:...) permitiendo embeber ficheros en datos serializados.

- **DateIntervalNormalizer**

Convierte objetos DateInterval en strings. Por defecto utiliza el formato P%yY%mM%dDT%hH%iM%sS.

## Encoders

Los codificadores convierten arrays en XML, JSON... y viceversa.

Todos implementan el interfaz **EncoderInterface** para codificar (array a formato) y **DecoderInterface** para decodificar (formato a array).

### Built-in Encoders

Symfony viene con unos cuantos Encoders:

- JsonEncoder

- XmlEncoder

- YamlEncoder

- CsvEncoder

```php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

$encoders = array(new XmlEncoder(), new JsonEncoder());
$serializer = new Serializer(array(), $encoders);
```

## Grupos de atributos

Muchas veces queremos serializar diferentes juegos de atributos de nuestras entidades. Por ejemplo, en una API REST, queremos que los datos que se devuelvan de un producto sean distintos según la pantalla en la que te encuentras o según el rol del usuario, de forma que a un admin la API le devolverá más información sobre los objetos que a un usuario normal.

Esto se puede gestionar creando grupos de atributos:

Veamos un ejemplo en el que tenemos el siguiente objeto:

```php
use Symfony\Component\Serializer\Annotation\Groups;

class Product
{

    public $name;

    public $price;

    public $itemsSold;

    public $commission;
}
```

Podemos definir los grupos con anotaciones con XML o con YAML.

```php
use Symfony\Component\Serializer\Annotation\Groups;

class Product
{
    /**
     * @Groups({"admins", "affiliates", "users"})
     */
    public $name;

    /**
     * @Groups({"admins", "affiliates", "users"})
     */
    public $price;

    /**
     * @Groups({"admins"})
     */
    public $itemsSold;

    /**
     * @Groups({"admins", "affiliates"})
     */
    public $commission;
}
```

Una vez definidos los grupos, podemos elegir uno o más a la hora de serializar:

```php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;

$product = new Product();
$product->itemsSold = 20;
$product->commission = 7.5;
$product->price = 19.99;

$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
$normalizer = new PropertyNormalizer($classMetadataFactory);
$serializer = new Serializer([$normalizer]);

$data = $serializer->normalize($product, null, ['groups' => ['admins']]);

$data = $serializer->normalize($product, null, ['groups' => ['affiliates']]);

$data = $serializer->normalize($product, null, ['groups' => ['users']]);

$data = $serializer->normalize($product, null, ['groups' => ['affiliates', 'users']]);
```

Si se elige más de un grupo, el resultado será la fusión de las propiedades de ambos grupos.

Lo único extraño en el código anterior es que primero debemos inicializar la clase ClassMetadataFactory de alguna de las siguientes maneras:


```php
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
// For annotations
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
// For XML
// use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
// For YAML
// use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader;

$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
// For XML
// $classMetadataFactory = new ClassMetadataFactory(new XmlFileLoader('/path/to/your/definition.xml'));
// For YAML
// $classMetadataFactory = new ClassMetadataFactory(new YamlFileLoader('/path/to/your/definition.yaml'));
```

Por lo demás, todo muy sencillo e intuitivo.

NOTA
Para utilizar el loader *annotation*, debemos tener instalados los packages *doctrine/annotations* y *doctrine/cache*.

### Seleccionar atributos específicos

También es posible serializar atributos concretos sin haberlos configurado previamente.

```php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class User
{
    public $familyName;
    public $givenName;
    public $company;
}

class Company
{
    public $name;
    public $address;
}

$company = new Company();
$company->name = 'Les-Tilleuls.coop';
$company->address = 'Lille, France';

$user = new User();
$user->familyName = 'Dunglas';
$user->givenName = 'Kévin';
$user->company = $company;

$serializer = new Serializer(array(new ObjectNormalizer()));

$data = $serializer->normalize($user, null, array('attributes' => array('familyName', 'company' => ['name'])));
// $data = array('familyName' => 'Dunglas', 'company' => array('name' => 'Les-Tilleuls.coop'));
```

Si además se indica algún grupo, solamente se pueden indicar atributos que pertenezcan a dicho grupo.

El mismo mecanismo aplica en la desserialización.

## Convertir nombres de propiedades al serializar y des-serializar

Si necesitamos renombrar propiedades, symfony nos proporciona una manera para hacerlo: El systema Name Converter.

Pongamos que tenemos el siguiente objeto:

```php
class Company
{
    public $name;
    public $address;
}
```

Y queremos prefijar todos los atributos con el prefijo *org_*:

{"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}

Para este propósito se debe utilizar un **Name Converter** personalizado. 

Un Name Converter es una clase que implementa el interfaz **NameConverterInterface**.

```php
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class OrgPrefixNameConverter implements NameConverterInterface
{
    public function normalize($propertyName)
    {
        return 'org_'.$propertyName;
    }

    public function denormalize($propertyName)
    {
        return 'org_' === substr($propertyName, 0, 4) ? substr($propertyName, 4) : $propertyName;
    }
}
```

Los *name converter* se pasan como segundo parámetro de los normalizers:

```php
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

$nameConverter = new OrgPrefixNameConverter();
$normalizer = new ObjectNormalizer(null, $nameConverter);

$serializer = new Serializer(array($normalizer), array(new JsonEncoder()));

$company = new Company();
$company->name = 'Acme Inc.';
$company->address = '123 Main Street, Big City';

$json = $serializer->serialize($company, 'json');
// {"org_name": "Acme Inc.", "org_address": "123 Main Street, Big City"}
$companyCopy = $serializer->deserialize($json, Company::class, 'json');
```

### El name converter CamelCaseToSnakeCaseNameConverter

Symfony viene con un name converter denominado **CamelCaseToSnakeCaseNameConverter** que convierte los nombres de tipo CamelCase a SnakeCase.

```php
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

$normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());

class Person
{
    private $firstName;

    public function __construct($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }
}

$kevin = new Person('Kévin');
$normalizer->normalize($kevin);
// ['first_name' => 'Kévin'];

$anne = $normalizer->denormalize(array('first_name' => 'Anne'), 'Person');
// Person object with firstName: 'Anne'
```

A partir de Symfony 4.2, existe una anotación para definir los nombres de las propiedades:

```php
namespace App\Entity;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Person
{
    /** @SerializedName("customer_name") */
    private $firstName;

    public function __construct(string $firstName)
    {
        $this->firstName = $firstName;
    }

    // ...
}
```

Cuando se serialice esta entidad, la propiedad $firstName será llamada custormer_name en lugar de first_name.

```php
$serialized = $serializer->serialize(new Person('Jane'));
// {"customer_name": "Jane"}
```

## ¿Propiedades private o protected?

Si quieres serializar ENTIDADES tienes que declarar las propiedades como *protected*. La razón es porque is serializas una clase que ha sido anteriormente una instancia de un proxy, las variables *private* no serán serializadas y se lanzará un *PHP Notice*.

## Los métodos __sleep() y __wakeup() de PHP

No hay que olvidar la utilidad de los métodos __sleep() y __wakeup() de PHP. Son métodos que se ejecutan al serializar (__sleep()) y deserializar (__wakeup()) una clase que contenga dichos métodos.