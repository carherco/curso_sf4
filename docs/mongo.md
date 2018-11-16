# Instalación

https://medium.com/@ahmetmertsevinc/symfony-4-and-doctrine-mongo-db-c9ac0f02f742

https://www.franciscougalde.com/2018/03/26/como-configurar-mongodb-en-symfony-4/

## Persistir documentos

```php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use App\Document\Product;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
  public function createAction(DocumentManager $dm)
  {
      $product = new Product();
      $product->setName('A Foo Bar');
      $product->setPrice('19.99');

      //$dm = $this->get('doctrine_mongodb')->getManager();
      $dm->persist($product);
      $dm->flush();

      return new Response('Created product id '.$product->getId());
  }
}
```

NOTA: para editar objetos o borrarlos, no hace falta $dm->persist($product);

## Query builder

```php
$products = $this->get('doctrine_mongodb')
    ->getManager()
    ->createQueryBuilder('AcmeStoreBundle:Product')
    ->field('name')->equals('foo')
    ->sort('price', 'ASC')
    ->limit(10)
    ->getQuery()
    ->execute()
```

## Generar repositorios

php bin/console doctrine:mongodb:generate:repositories AcmeStoreBundle

## User (security)

Es exactamente igual que con doctrine normal:

```yml
security:
    providers:
        my_mongo_provider:
            mongodb: {class: Acme\DemoBundle\Document\User, property: username}
```

## Abstraction Layer

Tenemos disponible una capa con los mismos métodos que se documentan en http://www.php.net/manual/en/mongo.tutorial.php

```php
// connect
$m = $this->container->get('doctrine_mongodb.odm.default_connection');

// select a database
$db = $m->selectDatabase('comedy');

// select a collection (analogous to a relational database's table)
$collection = $db->createCollection('cartoons');

// add a record
$document = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );
$collection->insert($document);

// find everything in the collection
$cursor = $collection->find();
```

## Mapeo de Documentos

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTime;

/** @ODM\MappedSuperclass */
abstract class BaseEmployee
{
    /** @ODM\Id */
    private $id;

    /** @ODM\Field(type="int", strategy="increment") */
    private $changes = 0;

    /** @ODM\Field(type="collection") */
    private $notes = array();

    /** @ODM\Field(type="string") */
    private $name;

    /** @ODM\Field(type="int") */
    private $salary;

    /** @ODM\Field(type="date") */
    private $started;

    /** @ODM\Field(type="date") */
    private $left;

    /** @ODM\EmbedOne(targetDocument="Address") */
    private $address;

    public function getId() { return $this->id; }

    public function getChanges() { return $this->changes; }
    public function incrementChanges() { $this->changes++; }

    public function getNotes() { return $this->notes; }
    public function addNote($note) { $this->notes[] = $note; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getSalary() { return $this->salary; }
    public function setSalary($salary) { $this->salary = (int) $salary; }

    public function getStarted() { return $this->started; }
    public function setStarted(DateTime $started) { $this->started = $started; }

    public function getLeft() { return $this->left; }
    public function setLeft(DateTime $left) { $this->left = $left; }

    public function getAddress() { return $this->address; }
    public function setAddress(Address $address) { $this->address = $address; }
}

/** @ODM\Document */
class Employee extends BaseEmployee
{
    /** @ODM\ReferenceOne(targetDocument="Documents\Manager") */
    private $manager;

    public function getManager() { return $this->manager; }
    public function setManager(Manager $manager) { $this->manager = $manager; }
}

/** @ODM\Document */
class Manager extends BaseEmployee
{
    /** @ODM\ReferenceMany(targetDocument="Documents\Project") */
    private $projects;

    public function __construct() { $this->projects = new ArrayCollection(); }

    public function getProjects() { return $this->projects; }
    public function addProject(Project $project) { $this->projects[] = $project; }
}

/** @ODM\EmbeddedDocument */
class Address
{
    /** @ODM\Field(type="string") */
    private $address;

    /** @ODM\Field(type="string") */
    private $city;

    /** @ODM\Field(type="string") */
    private $state;

    /** @ODM\Field(type="string") */
    private $zipcode;

    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    public function getCity() { return $this->city; }
    public function setCity($city) { $this->city = $city; }

    public function getState() { return $this->state; }
    public function setState($state) { $this->state = $state; }

    public function getZipcode() { return $this->zipcode; }
    public function setZipcode($zipcode) { $this->zipcode = $zipcode; }
}

/** @ODM\Document */
class Project
{
    /** @ODM\Id */
    private $id;

    /** @ODM\Field(type="string") */
    private $name;

    public function __construct($name) { $this->name = $name; }

    public function getId() { return $this->id; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }
}
```


Ejemplo de uso:


```php
<?php

use Documents\Employee;
use Documents\Address;
use Documents\Project;
use Documents\Manager;
use DateTime;

$employee = new Employee();
$employee->setName('Employee');
$employee->setSalary(50000);
$employee->setStarted(new DateTime());

$address = new Address();
$address->setAddress('555 Doctrine Rd.');
$address->setCity('Nashville');
$address->setState('TN');
$address->setZipcode('37209');
$employee->setAddress($address);

$project = new Project('New Project');
$manager = new Manager();
$manager->setName('Manager');
$manager->setSalary(100000);
$manager->setStarted(new DateTime());
$manager->addProject($project);

$dm->persist($employee);
$dm->persist($address);
$dm->persist($project);
$dm->persist($manager);
$dm->flush();
```
