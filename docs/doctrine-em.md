# Entity Manager

El entity manager es un servicio muy utilizado en Doctrine.

A continuación se describe la API de este servicio.

## Find

```php
$article = $entityManager->find('Entity\Article', 1234);
```

## Persist

Al aplicar la operación **persist()** en una entidad, dicha entidad pasa a estar *manejada* por el EntityManager.

```php
$user = new User;
$user->setName('Mr.Right');
$em->persist($user);
```

## Detach

El método **detach()** se utiliza para que una entidad manejada por el EntityManager deje de estarlo.

Si dicha entidad no estaba siendo manejada por el EntityManager, aplicar detach no provoca ningún efecto.

```php
$em->detach($entity);
```

## Flush

El método flush() provoca la sincronización con la bbdd de las entidades que están siendo manejadas.

```php
$em->flush();
```

En caso de operaciones de UPDATE, el Entity Manager sabe qué propiedades de cada entidad han sido modificadas. Únicamente se ejecutarán sentencias UPDATE de las entidades modificadas y solamente de las propiedades modificadas.

## Remove

Es el método que tenemos para indicar al EntityManager que queremos eliminar un registro de la bbdd.

Si aplicamos el método remove() a una entidad, la entidad pasará a estar manejada por el EntityManager y será eliminada de la bbdd cuando se invoque el método flush.

```php
$em->remove($user);
```

## Clear

El método **clear()** equivale a hacer un detach() de todas las entidades.

```php
// Detaches all objects from Doctrine!
$em->clear();
```

## Refresh

Refresca el "estado persistente" de una entidad (el que hay en la base de datos) anulando los cambios que todavía no se hubieran persistido.

## Merge

Merge se utiliza para coger una entidad que ha sido "detached" y hacerle "reattach".

Si la entidad nunca había sido manejada por el Entity Manager, entonces merge es equivalente a persist.

Si la entidad había sido "detached" o serializada (puesta en la caché, quizás), merge más o menos lo que hace es buscar el id de la entidad en el data store y empezar a trackear cualquier cambio en dicha entidad desde ese punto.

https://gist.github.com/sentenza/739205fb70511372e260cef943df7530

## Unit of work

El coste de una operación flush depende de dos factores:

- El tamaño de la UnitOfWork actual del EntityManager.
- La política de *tracking* de cambios configurada

El tamaño de la UnitOfWork lo podemos conocoer de la siguiente manera

```php
$uowSize = $em->getUnitOfWork()->size();
```

### Conocer el estado de una entidad

Le podemos preguntar al UnitOfWork cual es el estado de una entidad

```php
switch ($em->getUnitOfWork()->getEntityState($entity)) {
    case UnitOfWork::STATE_MANAGED:
        ...
    case UnitOfWork::STATE_REMOVED:
        ...
    case UnitOfWork::STATE_DETACHED:
        ...
    case UnitOfWork::STATE_NEW:
        ...
}
```

- MANAGED: si está asociada con un EntityManager y no está REMOVED.

- REMOVED: cuando se ha aplicado el método remove().

- DETACHED: si tiene *persistent state and identity* pero no está actualmente asociada con un EntityManager.

- NEW: si NO tiene *persistent state and identity* y no está actualmente asociada con un EntityManager (normalmente entidades recién creadas con *new*).

## Bulk inserts

Si tenemos que gestionar, por ejemplo, un proceso que genera 10.000 inserts, podemos aprovecharnos de los *bulk inserts* de Doctrine.

El siguiente código muestra un ejemplo que realiza 10.000 inserts con un tamaño de *batch* de 20.

Experimentando, podemos encontrar el tamaño de batch que mejor se adecúe a nuestro caso. Tamaños mayores implican mayor reutilización interna del prepared statement pero implican mayor trabajo durante cada flush().

```php
$batchSize = 20;
for ($i = 1; $i <= 10000; ++$i) {
    $user = new CmsUser;
    $user->setStatus('user');
    $user->setUsername('user' . $i);
    $user->setName('Mr.Smith-' . $i);
    $em->persist($user);
    if (($i % $batchSize) === 0) {
        $em->flush();
        $em->clear(); // Detaches all objects from Doctrine!
    }
}
$em->flush(); //Persist objects that did not make up an entire batch
$em->clear();
```

## Iterar sobre gran cantidad de objetos

En el caso de un select con muchos resultados, SIN join, y SIN intención de ejecutar UPDATES ni DELETES, podemos utilizar la herramienta **iterator()** para evitar problemas de memoria.

```php
$q = $this->_em->createQuery('select u from User u');
$iterableResult = $q->iterate();
foreach ($iterableResult as $row) {
    // do stuff with the data in the row, $row[0] is always the object

    // detach from Doctrine, so that it can be Garbage-Collected immediately
    $this->_em->detach($row[0]);
}
```

Hemos conseguido los datos con una única consulta, pero el método iterate() hidrata las entidades incrementalmente (de una en una).

La hidratación incremental mete a la entidad automáticamente en la gestión del entity manager, por lo que hay que hacer detach de las entidades si no queremos sobrecargarlo.

## Actualizaciones masivas / Eliminaciones masivas

Para las actualizaciones/eliminaciones masivas se aconseja utilizar DQL:

```php
$qb->update('App:User', 'u')
    ->set('u.active', 1)
    ->getQuery()->execute();
```

De esta forma se ejecuta una única SQL UPDATE en lugar de ejecutarse una sentencia SELECT y N sentencias UPDATE.

O si necesitamos las entidades porque necesitamos ejecutar algún método o por alguna otra razón, se puede recurrir de nuevo a iterate().

```php
$batchSize = 20;
$q = $this->_em->createQuery('select u from User u');
$iterableResult = $q->iterate();
foreach ($iterableResult as $row) {
    $user = new CmsUser;
    $user->increaseCredit();
    $user->calculateNewBonuses();
    if (($i % $batchSize) === 0) {
        $em->flush();
        $em->clear();
    }
}
$em->flush();
$em->clear();
```

Recordatorio: iterate() no sirve si hay JOIN.

https://issuu.com/migueleduardocarmonalugo/docs/manual_doctrine_completo_espanol (sección 1.12)

## Change Tracking Policies

El seguimiento de cambios es el proceso de determinar qué ha cambiado en entidades gestionadas desde la última vez que se sincronizan con la base de datos.

Doctrina ofrece 3 políticas de seguimiento de cambios diferentes, cada uno con sus ventajas y desventajas particulares. La política de seguimiento de los cambios se puede definir en función de cada clase.

## Deferred Implicit

La política *Deferred Implicit* es la política de control de cambios por defecto y la más conveniente. Con esta política, Doctrine detecta los cambios comparando propiedad por propiedad todas las entidades manejadas y **también aquellas entidades asociadas a las manejadas**. A pesar de ser la política más conveniente, puede tener efectos negativos en el rendimiento si se trata de grandes unidades de trabajo. Cada vez que se invoca EntityManager#flush() se tienen que comprobar todas las entidades gestionadas, por lo que esta operación puede ser bastante costosa.

## Deferred Explicit

La política *Deferred Explicit* es similar a la política *Deferred Implicit* porque detecta cambios a través de una comparación propiedad por propiedad. La diferencia es que Doctrine sólo tiene en cuenta las entidades que se han marcado explícitamente para la detección de cambio con persist() y las que tengan configurado *save en cascada*. El resto de entidades son ignoradas.

Esta política puede ser configurada de la siguiente manera:

```php
/**
 * @Entity
 * @ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class User
{
    // ...
}
```

## Notify

Esta política asume que las entidades notifican de los cambios en sus propiedades. Una entidad con esta política debe implementar la interfaz **Doctrine\Common\NotifyPropertyChanged** que consiste en un método **addPropertyChangedListener()**.

```php
use Doctrine\Common\NotifyPropertyChanged,
    Doctrine\Common\PropertyChangedListener;

/**
 * @Entity
 * @ChangeTrackingPolicy("NOTIFY")
 */
class MyEntity implements NotifyPropertyChanged
{
    // ...

    private $_listeners = array();

    public function addPropertyChangedListener(PropertyChangedListener $listener)
    {
        $this->_listeners[] = $listener;
    }
}
```

En cada método setter, tenemos que noficar a todos nuestros listeners del cambio producido. Los Listeners implementan **PropertyChangedListener** lo que nos asegura que disponen de un método **propertyChanged()**.

```php
// ...

class MyEntity implements NotifyPropertyChanged
{
    // ...

    protected function _onPropertyChanged($propName, $oldValue, $newValue)
    {
        if ($this->_listeners) {
            foreach ($this->_listeners as $listener) {
                $listener->propertyChanged($this, $propName, $oldValue, $newValue);
            }
        }
    }

    public function setData($data)
    {
        if ($data != $this->data) {
            $this->_onPropertyChanged('data', $this->data, $data);
            $this->data = $data;
        }
    }
}
```

http://www.inanzzz.com/index.php/post/qc6v/taking-logs-with-notify-change-tracking-policy-when-a-property-value-of-an-entity-is-changed
