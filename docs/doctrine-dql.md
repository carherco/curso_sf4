El lenguaje DQL (Doctrine Query Language)
=========================================

Si queremos ahorrar consultas, o realizar consultas complejas, podemos utilizar el leguaje DQL.

```php
class AlumnoRepository extends \Doctrine\ORM\EntityRepository
{
  public function findWithNotas($id)
  {
      $query = $this->getEntityManager()
          ->createQuery(
          'SELECT a, n, g, asig FROM App:Alumno a
          JOIN a.notas n
          JOIN a.grado g
          JOIN n.asignatura asig
          WHERE a.id = :id'
      )->setParameter('id', $id);

      try {
          return $query->getSingleResult();
      } catch (\Doctrine\ORM\NoResultException $e) {
          return null;
      }
  }
}
```

En el ejemplo anterior, en una única consulta SQL obtenemos el alumno, su grado, sus notas y las asignaturas de sus notas.

Se puede utilizar DQL para consultas de tipo SELECT, UPDATE y DELETE.

DQL no permite hacer INSERTS. Los inserts se deben realizar mediante el método persist() del EntityManager.

En las consultas de tipo SELECT con DQL, Doctrine hidrata los resultados en las Entidades correspondientes.

Tipos de respuesta
------------------

```php
$result = $query->getResult();
$single = $query->getSingleResult();
$array = $query->getArrayResult();
$scalar = $query->getScalarResult();
$singleScalar = $query->getSingleScalarResult();
```

SELECT
------

Cuando trabajamos con DQL, no trabajamos con tablas y campos de las tablas sino con entidades y con propiedades de las entidades.

```php
$query = $em->createQuery('SELECT u FROM Entity\User u WHERE u.age > 20');
$users = $query->getResult();
```

Formato de la respuesta
-----------------------

```sql
SELECT u, p, n FROM Users u...
```

En este caso Doctrine devuelve un array de objetos Users, con las entidades relacionadas p y n ya hidratadas porque forman parte del SELECT.

```sql
SELECT u.name, u.address FROM Users u...
```

En este caso Doctrine devuelve un array de arrays.

```sql
SELECT u, p.quantity FROM Users u...
```

En este caso Doctrine devuelve un array con una mezcla de objetos y valores.

JOINS
-----

Los joins se realizan a través de las propiedades de las entidades

```php
<?php
$query = $em->createQuery("SELECT u FROM User u JOIN u.address a WHERE a.city = 'Berlin'");
$users = $query->getResult();
```

PARÁMETROS
----------

Se pueden indicar parámetros con el símbolo '?' o el símbolo ':'

```php
<?php
$query = $em->createQuery('SELECT u FROM ForumUser u WHERE u.id = ?1');
$query->setParameter(1, 321);
$users = $query->getResult();
```

```php
<?php
$query = $em->createQuery('SELECT u FROM ForumUser u WHERE u.username = :name');
$query->setParameter('name', 'Bob');
```

```php
<?php
$query = $em->createQuery('SELECT u FROM ForumUser u WHERE (u.username = :name OR u.username = :name2) AND u.id = :id');
$query->setParameters(array(
    'name' => 'Bob',
    'name2' => 'Alice',
    'id' => 321,
));
$users = $query->getResult();
```

Es importante parametrizar las consultas:

- Protege contra SQL INJECTION
- Crea *Prepared Statements* que se pueden reutilizar mejorando así el rendimiento

FUNCIONES
---------

En las clausulas SELECT, WHERE y HAVING se pueden utilizar las siguientes funciones de DQL:

- IDENTITY(single\_association\_path\_expression [, fieldMapping]) - Devuelve la columna foreign key de la asociación
- ABS(arithmetic\_expression)
- CONCAT(str1, str2)
- CURRENT\_DATE()
- CURRENT\_TIME()
- CURRENT\_TIMESTAMP()
- LENGTH(str)
- LOCATE(needle, haystack [, offset]) - Busca la primera ocurrencia de un substring en un string.
- LOWER(str)
- MOD(a, b)
- SIZE(collection) - Devuelve el número de elementos de la colección
- SQRT(q) - Raíz cuadrada
- SUBSTRING(str, start [, length])
- TRIM([LEADING \ BOTH] ['trchar' FROM] str)
- UPPER(str)
- DATE_ADD(date, days, unit)
- DATE_SUB(date, days, unit)
- DATE_DIFF(date1, date2)

Se pueden utilizar operadores matemáticos

```sql
SELECT person.salary * 1.5 FROM CompanyPerson person WHERE person.salary < 100000
```

En las clausulas SELECT y GROUP BY se pueden utilizar las siguientes funciones de agregado:

- AVG()
- COUNT()
- MIN()
- MAX()
- SUM()

Y por último, las expresiones de SQL soportadas por DQL son:

- ALL/ANY/SOME
- BETWEEN a AND b y NOT BETWEEN a AND b
- IN (x1, x2, ...) y NOT IN (x1, x2, ..)
- LIKE .. y NOT LIKE ..
- IS NULL y IS NOT NULL
- EXISTS y NOT EXISTS
