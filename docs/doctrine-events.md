# Events

En este enlace está la lista completa de eventos: https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/events.html#lifecycle-events

## Lifecycle Callbacks

En nuestras entidades podemos definir funciones de callback para los eventos

```php
/** @Entity @HasLifecycleCallbacks */
class User
{
    // ...

    /**
     * @Column(type="string", length=255)
     */
    public $value;

    /** @Column(name="created_at", type="string", length=255) */
    private $createdAt;

    /** @PrePersist */
    public function doStuffOnPrePersist()
    {
        $this->createdAt = date('Y-m-d H:i:s');
    }

    /** @PrePersist */
    public function doOtherStuffOnPrePersist()
    {
        $this->value = 'changed from prePersist callback!';
    }

    /** @PostPersist */
    public function doStuffOnPostPersist()
    {
        $this->value = 'changed from postPersist callback!';
    }

    /** @PostLoad */
    public function doStuffOnPostLoad()
    {
        $this->value = 'changed from postLoad callback!';
    }

    /** @PreUpdate */
    public function doStuffOnPreUpdate()
    {
        $this->value = 'changed from preUpdate callback!';
    }
}
```

Desde la versión 2.4 de Doctrine, las funciones de Callback reciben un argumento con información sobre el evento

```php
class User
{
    /** @PreUpdate */
    public function doStuffOnPreUpdate(PreUpdateEventArgs $event)
    {
        if ($event->hasChangedField('username')) {
            // Do something when the username is changed.
        }
    }
}
```

Además de los callbacks en las propias entidades, podemos suscribir Listeners o Subscribers fuera de las entidades.

```php
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class MyEventSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            Events::postUpdate,
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof Product) {
            // do something with the Product
        }
    }
```

```php
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class MyEventListener
{
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof Product) {
            // do something with the Product
        }
    }
}
```

Consideraciones sobre los listeners/subscribers:

- Los listeners/subscribers se ejecutarán en los eventos de todas las entidades
- Los listeners son lazy, los subscribers no.

### Restricciones en los listeners/suscribers

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/events.html#implementing-event-listeners

### Es posible definir Listeners específicos para una entidad

https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/events.html#implementing-event-listeners

### Enlaces de interés:

https://symfony.com/doc/current/doctrine/lifecycle_callbacks.html

https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/events.html#lifecycle-events

https://symfony.com/doc/current/doctrine/event_listeners_subscribers.html
