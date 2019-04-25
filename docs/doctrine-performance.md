# Consejos sobre Rendimiento

## Revisar los tiempos de las consultas en el Profiler

## Evitar cargar entidades en bucles foreach

Por defecto doctrine no hidrata las entidades relacionadas. Una situación muy común es tener una asociación N-1 (por ejemplo, artículos de un blog, con su autor) y queremos listar los artículos con el nombre del autor.

El código suele ser parecido a este:

```php
$em = $this->getEntityManager();

$articlesRepo = $em->getRepository('App:Article');
$articles = $articlesRepo->findAll();
```

y en la vista

```html
{% for article in articles %}
    {{ article.author.name }}
    {{ article.content }}
{% endfor %}
```

Este código realizará una nueva SELECT por cada autor de cada artículo.

En su lugar, hay que pedir a Doctrine que hidrate ambas entidades declarándolas en el apartado SELECT

```php
$qb = $em->createQueryBuilder('qb1');
$qb->add('select', 'a, au')
        ->add('from', 'App:Article a')
        ->join('a.author', 'au');
```

De esta forma, en una única consulta obtendremos los artículos y sus autores.

## Evitar la hidratación si se manejan muchas entidades y solamente los necesitamos para lectura

El proceso de hidratación es uno de los procesos que más tiempo y memoria consumen en Doctrine.

Si estamos obteniendo registros de la base de datos únicamente para enviarlos a una vista / json, hidratarlos a entidades puede no tener mucho sentido.

En lugar de eso, se pueden hidratar a estructuras más sencillas como arrays o scalar:

```php
$qb = new \Doctrine\ORM\QueryBuilder;

$arrayResults  = $qb->getQuery()->getArrayResult();
$scalarResults = $qb->getQuery()->getScalarResult();
```

## Conflicto entre los dos consejos anteriores

En asociaciones N-1 y 1-1 la hidratación, aún siendo un proceso que consume bastante tiempo y memoria es bastante eficiente.

En asociaciones N-N y 1-N la hidratación no es eficiente si incluímos las dos partes del Join en el Select para que doctrine realizce una única consulta y tengamos las entidades relacionadas también hidratadas. 

> SELECT c, e FROM App\Entity\Company c JOIN company.emplyoees e

Pero si utilizamos **lazy loading**, el bucle posterior en twig o en el controlador nos llevaría a realizar muchas consultas.

> SELECT c FROM App\Entity\Company c JOIN company.emplyoees e

En estos casos, debemos recurrir al **eager loading**:

```php
$dql = "SELECT c FROM App\Company c WHERE xxxxx";
$query = $entityManager->createQuery($dql);
$companies = $query->getResult();

$employees = $entityManager
    ->getRepository('App\Employee')
    ->findBy(array('company' => $companies));

$employeesByCompany = [];
foreach ($employees as $employee) {
    $companyId = $employee->getCompany()->getId();
    $employeesByCompany[$companyId][] = $employee;
}
```

Es decir, realizar 2 consultas, y enlazar las entidades con programación PHP

Se puede configurar la relación, para que Doctrine utilice esta técnica de Eager Loading con @ORM\ManyToOne(fetch="EAGER"). Esta solución por configuración es adecuada si casi siempre vamos a necesitar ambas partes de la relación.

La última solución para abordar este problema es cargar únicamente los datos que necesitemos, sin hidratar:

```sql
SELECT e.name as employee, c.name as company
FROM App\Employee e JOIN e.company c
```

## No cargar la entidad completa si solmente necesitamos su referencia

Muchas veces tenemos la situación de una entidad y el ID de otra entidad que queremos asociar a la primera. Hacer un find($id) de la segunda entidad para asociarla a la primera es un SELECT extra innecesario.

Gracias a los **Doctrine’s Reference Proxies** no es necesario obtener la entidad y podemos trabajar únicamente con su ID:

```php
$em = ...; // the \Doctrine\ORM\EntityManager instance
$friendId = ...; // ID of other user entity, obtained from anywhere - e.g. request

$user = new User;
$user->addFriend($em->getReference('App\Entity\User', $friendId));

$em->persist($user);
$em->flush();
```

https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/advanced-configuration.html#reference-proxies

## Utilizar la sentencia Update para actualizaciones masivas

Se debe evitar esto:

```php
$friend = $em->getReference('App\Entity\User', $friendId);
$users = $this->findAll();

foreach ($users as $user) {
    $user->setFriend($friend);
    $em->persist($user);
}

$em->flush();
```

Y en su lugar utilizar la sentencia update:

```php
$qb->update('App:User', 'u')
    ->set('u.friend', $friendId)
    ->getQuery()->execute();
```

De esta forma se ejecuta una única SQL UPDATE en lugar de ejecutarse una sentencia SELECT y N sentencias UPDATE.

## Lazy Collections

Si tenemos asociaciones ManyToMany o OneToMany definidos con fetch=EXTRA_LAZY

```php
/**
 * @Entity
 */
class CmsGroup
{
    /**
     * @ManyToMany(targetEntity="CmsUser", mappedBy="groups", fetch="EXTRA_LAZY")
     */
    public $users;
}
```

conseguimos que al ejecutar métodos como count() o slice()

```php
$users->count();
$users->slice(…)
```

no se cargue la colección entera de la base de datos a la memoria. En lugar de eso, Doctrine realizará las querys necesarias con COUNT(), LIMIT, etc.

https://www.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/extra-lazy-associations.html#extra-lazy-associations

## No cargar todos los datos de la entidad si no los vamos a utilizar

Hay veces que cargamos todos los datos una entidad únicamente para obtener un dato concreto, y la entidad no se utilizar para nada más.

```php
function getUserAge($id) {
  $em = $this->getDoctrine()->getManager();
  $repo = $em->getRepository('App:Users');
  $user = $repo->find($id);
  $age = $user->getAge();

  return $age;
}
```

En su lugar podríamos hacer lo siguiente:

```php
function getUserAge($id) {
  $em = $this->getDoctrine()->getManager();
  $repo = $em->getRepository('App:Users');
  $age = $repo->createQuery(
      'SELECT z.age'.
      'FROM ourcodeworldBundle:Users z'.
      'WHERE z.id = :id'
  )
  ->setParameter('id',2)
  ->getSingleScalarResult();

  return $age;
}
```

## Entidades de solo lectura

Desde doctrine 2.1 es posible marcar entidades como de sólo lectura (read only). Estas entidades nunca se considerarán para updates, el EntityManager las ignorará en el flush().

Si que se permite persistir entidades NUEVAS (INSERT) o eliminar entidades (DELETE). Simplemente no se pueden cambiar (UPDATE).

## Multi-step Hydration

Cunado tenemos una consulta con múltiples JOINS, dicha consulta se puede dividir en varias consultas y conseguir que doctrine no vuelva a hidratar las entidades ya hidratadas.

Lo podemos ver con la siguiente consulta como ejemplo:

```php
return $entityManager
    ->createQuery('
        SELECT
            user, socialAccounts, sessions 
        FROM
            User user
        LEFT JOIN
            user.socialAccounts socialAccounts
        LEFT JOIN
            user.sessions sessions
    ')
    ->getResult();
```

El código anterior hidrataría user, socialAccounts y sessions de una única vez, siendo muy ineficiente ya que debe reconocer y evitar las repeticiones de registros socialAccounts y sessions.

La hidratación multi-step se consigue de la siguiente forma:

```php
$users = $entityManager
    ->createQuery('
        SELECT
            user, socialAccounts
        FROM
            User user
        LEFT JOIN
            user.socialAccounts socialAccounts
    ')
    ->getResult();

$entityManager
    ->createQuery('
        SELECT PARTIAL
            user.{id}, sessions
        FROM
            User user
        LEFT JOIN
            user.sessions sessions
    ')
    ->getResult(); // result is discarded (this is just re-hydrating the collections)

return $users;
```

Con la primera consulta, se hidratan los usuarios y los socialAccounts. Con la segunda consulta se hidratan las sessions en las entidades user previamente hidratadas.

https://github.com/Ocramius/Doctrine2StepHydration

## Listeners vs. Subscribers

Una diferencia importante entre listeners y subscribers es que Symfony carga de forma lazy los listeners de entidades. Esto significa que las clases listener solamente se cogen del *service container* y se instancian si el evento relacionado ha sido disparado.

Esta es la razón por la que es preferible utilizar *entity listeners* en vez de subscribers cuando sea posible.

https://symfony.com/doc/current/doctrine/event_listeners_subscribers.html#performance-considerations

## Buenas prácticas

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html