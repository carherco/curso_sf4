Ejecutar sentencias SQL directamente
====================================

En caso de encontrarnos alguna SQL que no sepamos realizar con DQL, podemos recurrir a realizarla con SQL.

```php
$em = $this->getDoctrine()->getManager();
$connection = $em->getConnection();
$statement = $connection->prepare("SELECT something FROM somethingelse WHERE id = :id");
$statement->bindValue('id', 123);
$statement->execute();
$results = $statement->fetchAll();
```

Evidentemente, si ejecutamos consultas de esta forma, no tendremos los datos en entidades, los tendremos en arrays de php.

Si queremos hidratar los resultados en entidades, tenemos que utilizar las denominadas Native SQL.

## Native SQL

Para crear sqls nativas se utiliza el método **EntityManager#createNativeQuery($sql, $resultSetMapping)**.

Como podemos ver, este método requiere de dos argumentos: la propia sql y un ResultSetMapping que describe cómo mapear los resultados en entidades.

Una vez obtenida una instancia de NativeQuery podemos *bindear* parámetros y ejecutarla.

```php
use Doctrine\ORM\Query\ResultSetMapping;

$rsm = new ResultSetMapping();
// build rsm here

$query = $entityManager->createNativeQuery('SELECT id, name FROM users WHERE name = ?', $rsm);
$query->setParameter(1, 'carlos');

$users = $query->getResult();
```

Un ejemplo de ResultSetMapping sería el siguiente

```php
// Equivalent DQL query: "select u from User u where u.name=?1"
// User owns no associations.
$rsm = new ResultSetMapping;
$rsm->addEntityResult('User', 'u');
$rsm->addFieldResult('u', 'id', 'id');
$rsm->addFieldResult('u', 'name', 'name');

$query = $this->_em->createNativeQuery('SELECT id, name FROM users WHERE name = ?', $rsm);
$query->setParameter(1, 'carlos');

$users = $query->getResult();
// Users será un array de Entity\User
```

Los usuarios del array resultante solamente tendrán los campos *id* y *name* con valores. El resto de campos serán nulos.

Otro ejemplo pero con entidades relacionadas

```php
// Equivalent DQL query: "select u from User u where u.name=?1"
// User owns an association to an Address but the Address is not loaded in the query.
$rsm = new ResultSetMapping;
$rsm->addEntityResult('User', 'u');
$rsm->addFieldResult('u', 'id', 'id');
$rsm->addFieldResult('u', 'name', 'name');
$rsm->addMetaResult('u', 'address_id', 'address');

$query = $this->_em->createNativeQuery('SELECT id, name, address_id FROM users WHERE name = ?', $rsm);
$query->setParameter(1, 'carlos');

$users = $query->getResult();
```

El ejemplo anterior permite llamar a $user->getAddress() y obtener la entidad relacionada. Al llamar a $user->getAddress() se ejecutará la SQL que obtiene los datos de la tabla address.

```php
// Equivalent DQL query: "select u, a from User u join u.address a WHERE u.name = ?1"
// User owns association to an Address and the Address is loaded in the query.
$rsm = new ResultSetMapping;
$rsm->addEntityResult('User', 'u');
$rsm->addFieldResult('u', 'id', 'id');
$rsm->addFieldResult('u', 'name', 'name');
$rsm->addJoinedEntityResult('Address' , 'a', 'u', 'address');
$rsm->addFieldResult('a', 'address_id', 'id');
$rsm->addFieldResult('a', 'street', 'street');
$rsm->addFieldResult('a', 'city', 'city');

$sql = 'SELECT u.id, u.name, a.id AS address_id, a.street, a.city FROM users u ' .
       'INNER JOIN address a ON u.address_id = a.id WHERE u.name = ?';
$query = $this->_em->createNativeQuery($sql, $rsm);
$query->setParameter(1, 'carlos');

$users = $query->getResult();
```

En este último ejemplo, los usuarios ya tienen las direcciones precargadas. Con $user->getAddress() tendremos la entidad Address sin necesidad de ejectuar otra SQL.

## Named Native Query

Se pueden definir native queries con anotaciones

```php
<?php
namespace MyProject\Model;
/**
 * @NamedNativeQueries({
 *      @NamedNativeQuery(
 *          name            = "fetchMultipleJoinsEntityResults",
 *          resultSetMapping= "mappingMultipleJoinsEntityResults",
 *          query           = "SELECT u.id AS u_id, u.name AS u_name, u.status AS u_status, a.id AS a_id, a.zip AS a_zip, a.country AS a_country, COUNT(p.phonenumber) AS numphones FROM users u INNER JOIN addresses a ON u.id = a.user_id INNER JOIN phonenumbers p ON u.id = p.user_id GROUP BY u.id, u.name, u.status, u.username, a.id, a.zip, a.country ORDER BY u.username"
 *      ),
 * })
 * @SqlResultSetMappings({
 *      @SqlResultSetMapping(
 *          name    = "mappingMultipleJoinsEntityResults",
 *          entities= {
 *              @EntityResult(
 *                  entityClass = "__CLASS__",
 *                  fields      = {
 *                      @FieldResult(name = "id",       column="u_id"),
 *                      @FieldResult(name = "name",     column="u_name"),
 *                      @FieldResult(name = "status",   column="u_status"),
 *                  }
 *              ),
 *              @EntityResult(
 *                  entityClass = "Address",
 *                  fields      = {
 *                      @FieldResult(name = "id",       column="a_id"),
 *                      @FieldResult(name = "zip",      column="a_zip"),
 *                      @FieldResult(name = "country",  column="a_country"),
 *                  }
 *              )
 *          },
 *          columns = {
 *              @ColumnResult("numphones")
 *          }
 *      )
 *})
 */
 class User
{
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;

    /** @Column(type="string", length=50, nullable=true) */
    public $status;

    /** @Column(type="string", length=255, unique=true) */
    public $username;

    /** @Column(type="string", length=255) */
    public $name;

    /** @OneToMany(targetEntity="Phonenumber") */
    public $phonenumbers;

    /** @OneToOne(targetEntity="Address") */
    public $address;

    // ....
}
```

Estas anotaciones permiten asociar de forma implícita las propiedades de las entidades con los campos de la SELECT.

```php
<?php
namespace MyProject\Model;
/**
  * @NamedNativeQueries({
  *      @NamedNativeQuery(
  *          name                = "findAll",
  *          resultSetMapping    = "mappingFindAll",
  *          query               = "SELECT * FROM addresses"
  *      ),
  * })
  * @SqlResultSetMappings({
  *      @SqlResultSetMapping(
  *          name    = "mappingFindAll",
  *          entities= {
  *              @EntityResult(
  *                  entityClass = "Address"
  *              )
  *          }
  *      )
  * })
  */
class Address
{
    /**  @Id @Column(type="integer") @GeneratedValue */
    public $id;

    /** @Column() */
    public $country;

    /** @Column() */
    public $zip;

    /** @Column()*/
    public $city;

    // ....
}
```

Y una tercera forma de mapear sería la siguiente

```php
<?php
namespace MyProject\Model;
/**
  * @NamedNativeQueries({
  *      @NamedNativeQuery(
  *          name            = "fetchJoinedAddress",
  *          resultSetMapping= "mappingJoinedAddress",
  *          query           = "SELECT u.id, u.name, u.status, a.id AS a_id, a.country AS a_country, a.zip AS a_zip, a.city AS a_city FROM users u INNER JOIN addresses a ON u.id = a.user_id WHERE u.username = ?"
  *      ),
  * })
  * @SqlResultSetMappings({
  *      @SqlResultSetMapping(
  *          name    = "mappingJoinedAddress",
  *          entities= {
  *              @EntityResult(
  *                  entityClass = "__CLASS__",
  *                  fields      = {
  *                      @FieldResult(name = "id"),
  *                      @FieldResult(name = "name"),
  *                      @FieldResult(name = "status"),
  *                      @FieldResult(name = "address.id", column = "a_id"),
  *                      @FieldResult(name = "address.zip", column = "a_zip"),
  *                      @FieldResult(name = "address.city", column = "a_city"),
  *                      @FieldResult(name = "address.country", column = "a_country"),
  *                  }
  *              )
  *          }
  *      )
  * })
  */
class User
{
    /** @Id @Column(type="integer") @GeneratedValue */
    public $id;

    /** @Column(type="string", length=50, nullable=true) */
    public $status;

    /** @Column(type="string", length=255, unique=true) */
    public $username;

    /** @Column(type="string", length=255) */
    public $name;

    /** @OneToOne(targetEntity="Address") */
    public $address;

    // ....
}
```

En caso de que queramos una sola entidad, podemos utilizar *resultClass* en vez de *resultSetMapping*

```php
<?php
namespace MyProject\Model;
/**
  * @NamedNativeQueries({
  *      @NamedNativeQuery(
  *          name           = "find-by-id",
  *          resultClass    = "Address",
  *          query          = "SELECT * FROM addresses"
  *      ),
  * })
  */
class Address
{
    // ....
}
```

Y finalmente, si el resultado es *scalar* y no tenemos que hidratar ninguna entidad

```php
namespace MyProject\Model;
/**
  * @NamedNativeQueries({
  *      @NamedNativeQuery(
  *          name            = "count",
  *          resultSetMapping= "mappingCount",
  *          query           = "SELECT COUNT(*) AS count FROM addresses"
  *      )
  * })
  * @SqlResultSetMappings({
  *      @SqlResultSetMapping(
  *          name    = "mappingCount",
  *          columns = {
  *              @ColumnResult(
  *                  name = "count"
  *              )
  *          }
  *      )
  * })
  */
class Address
{
    // ....
}
```