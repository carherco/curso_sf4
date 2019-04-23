# Workflows

El componente de Workflow proporciona utilidades para gestionar un flujo de trabajo o una máquina de estados.

Para instalar el componente de Workflow con Flex, hay que utilizar el siguiente comando:

> composer require symfony/workflow

## Creación de un Workflow

Un Workflow es un proceso o un ciclo de vida a través del cual se mueven nuestros objetos. Cada paso, fase, estado, etc en el proceso se denomina **place**. Los objetos cambian de un *place* a otro mediante **transitions**.

Para definir un Workflow por tanto, es necesario definir:

- **places**
- **transitions**

Veamos un ejemplo de configuración de un workflow:

```yml
# config/packages/workflow.yaml
framework:
    workflows: 
      publicacion_articulo:
            type: 'workflow' # o 'state_machine'
            audit_trail:
                enabled: true # La app genera mensajes de log detallados de la actividad del workflow
            marking_store:
                type: 'multiple_state' # o 'single_state'
                arguments: # Por defecto, 'marking'
                    - 'estado'
            supports:
                - App\Entity\Articulo
            places:
                - borrador
                - pendiente_de_correccion
                - pendiente_de_aprobacion
                - corregido
                - aprobado
                - rechazado
                - publicado
            initial_place: borrador
            transitions:
                revisar:
                    from: borrador
                    to:   [pendiente_de_correccion, pendiente_de_aprobacion]
                corregido:
                    from: 'pendiente_de_correccion'
                    to:   corregido
                aprobar:
                    from: 'pendiente_de_aprobacion'
                    to:   aprobado
                publicar:
                    from: [corregido, aprobado]
                    to:   publicado
                rechazar:
                    from: 'pendiente_de_aprobacion'
                    to:   rechazado
```

Este Workflow actuará sobre entidades *App\Entity\Articulo* y utilizará la propiedad *estado* para almacenar el *place* o los *places* de la entidad.

Si **marking_store** está definida como **multiple_state**, la entidad podrá estar en más de un place simultáneamente.

Si **marking_store** está definida como **single_state**, la entidad solamente podrá estar en un place simultáneamente.

Un ejemplo de entidad Articulo podría ser el siguiente:

```php
/**
 * Articulo
 *
 * @ORM\Table(name="articulo")}
 * @ORM\Entity
 */
class Articulo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=255, nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="string", length=255, nullable=false)
     */
    private $contenido;

    /**
     * Esta es la propiedad utilizada por el marking_store
     * 
     * @var array
     *
     * @ORM\Column(type="json_array", nullable=true) 
     */
    private $estado;
```

Si el workflow es single_state o state_machine, entonces la propiedad estado se definiría como string:

```php
/**
 * Articulo
 *
 * @ORM\Table(name="articulo")}
 * @ORM\Entity
 */
class Articulo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titulo", type="string", length=255, nullable=false)
     */
    private $titulo;

    /**
     * @var string
     *
     * @ORM\Column(name="contenido", type="string", length=255, nullable=false)
     */
    private $contenido;

    /**
     * Esta es la propiedad utilizada por el marking_store
     * 
     * @var string
     *
     * @ORM\Column(type="string", nullable=true) 
     */
    private $estado;
```

## Workflow vs. State Machine

Una máquina de estados en un subset de un workflow. Las diferencias más importantes son las que siguen:

- Los workflows pueden estar en más de un estado simultáneamente (multiple_state), las máquinas de estado no.
- Los workflows on suelen tener flujos cíclicos, las máquinas de estado si suelen ser cíclicas.
- Al aplicar una transición, un workflow requiere que el objeto esté en todos los *places* de la transición, mientras que una máquina de estados requiere que el objeto esté en al menos uno de esos lugares.

## Cómo trabajar con Workflows

Un workflow tiene varios métodos para facilitarnos trabajar con él. Inyectando el servicio correspondiente, podemos obtener el workflow asociado a un objeto:

```php
public function edit(Registry $workflows) {
  $workflow = $workflows->get($articulo);
}
```

Una vez tenemos el workflow, podemos utilizar sus métodos

- can($objeto, $transicion)

Devuevle *true* si se puede realizar la transición sobre el objeto.

- apply($objeto, $transicion)

Aplica una transición a un objeto

- getEnabledTransitions($objeto);

Devuelve un array con las posibles transiciones según el *place* actual del objeto.

Veamos un ejemplo:

```php
use Symfony\Component\Workflow\Registry;
use App\Entity\Articulo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Workflow\Exception\LogicException;

//...

/**
 * @Route("articulo/{id}/revisar", name="articulo_revisar")
 */
public function revisar(Registry $workflows, Articulo $articulo)
{
    $workflow = $workflows->get($articulo);

    if($workflow->can($articulo, 'revisar')) {
        try {
            $workflow->apply($articulo, 'revisar');
            $this->getDoctrine()->getManager()->flush();
        } catch (LogicException $exception) {
            // ... Si se intenta hacer una transición no válida
        }
    }

    return $this->redirectToRoute('articulo_index');
}
```

## Funciones de Twig

El componente Workflow define varias funciones de Twig para facilitar la programación de las plantillas:

- **workflow_can(objeto, transicion)**
Devuelve *true* si el objeto puede realizar la transición.

- **workflow_transitions(objeto)**
Devuelve un array con todas las transiciones posibles de un objeto según su estado actual.

- **workflow_marked_places(objeto)**
Devuelve un array con los nombres de los estados actuales de un objeto.

- **workflow_has_marked_place(objeto, estado)**
Devuelve *true* si el objeto tiene el estado.

En el siguiente bloque de código podemos ver algunos ejemplos de uso:

```html
<h3>Actions</h3>
{% if workflow_can(post, 'publish') %}
    <a href="...">Publish article</a>
{% endif %}
{% if workflow_can(post, 'to_review') %}
    <a href="...">Submit to review</a>
{% endif %}
{% if workflow_can(post, 'reject') %}
    <a href="...">Reject article</a>
{% endif %}

{# Recorrer las transiciones posibles de un objeto #}
{% for transition in workflow_transitions(post) %}
    <a href="...">{{ transition.name }}</a>
{% else %}
    No actions available.
{% endfor %}

{# Comprobar si un objeto está en un determinado estado #}
{% if workflow_has_marked_place(post, 'to_review') %}
    <p>This post is ready for review.</p>
{% endif %}

{# Obtener todos los estados de un objeto #}
{% if 'waiting_some_approval' in workflow_marked_places(post) %}
    <span class="label">PENDING</span>
{% endif %}
```

## Eventos

Para hacer nuestros workflows más flexibles y potentes, disponen de muchos eventos sobre los que actuar.

Cada paso en la transición de un estado a otro lanza 3 eventos:

- Un evento genérico para todos los workflows;
- Un evento para el workflow concreto;
- Un evento para el workflow concreto y la transición o estado concernientes.

Los eventos que se generan cada vez que se inicia una transición de estado son los siguientes, en este orden:

- **workflow.guard**
Valida si una transición es válida.

Los 3 eventos notificados son:

- workflow.guard
- workflow.[workflow name].guard
- workflow.[workflow name].guard.[transition name]

- **workflow.leave**
El *subject* (objeto) está a punto de salir de un *place*.

Los 3 eventos notificados son:

workflow.leave
workflow.[workflow name].leave
workflow.[workflow name].leave.[place name]

- **workflow.transition**

El objeto va a realizar una transición.

Los 3 eventos notificados son:

- workflow.transition
- workflow.[workflow name].transition
- workflow.[workflow name].transition.[transition name]

- **workflow.enter**

El objeto está a punto de entrar en un *place*. El place del objeto todavía no está actualizado.

Los 3 eventos notificados son:

- workflow.enter
- workflow.[workflow name].enter
- workflow.[workflow name].enter.[place name]

- **workflow.entered**

El objeto ha entrado en uno o más *places*. El objeto ya está actualizado. (Aquí podría ser un buen sitio para hacer flush de Doctrine).

Los 3 eventos notificados son:

- workflow.entered
- workflow.[workflow name].entered
- workflow.[workflow name].entered.[place name]

- **workflow.completed**

El objeto ha completado la transición.

Los 3 eventos notificados son:

- workflow.completed
- workflow.[workflow name].completed
- workflow.[workflow name].completed.[transition name]

- **workflow.announce**

Uno por cada transición que sea ahora accesible para el objeto.

Los 3 eventos notificados son:

- workflow.announce
- workflow.[workflow name].announce
- workflow.[workflow name].announce.[transition name]

NOTA
Los eventos se notifican al Dispatcher aunque la transición no haga cambiar el *place*.

### Ejemplo

Este ejemplo registra en el log los cambios de *place* de un objeto.

```php
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class WorkflowLogger implements EventSubscriberInterface
{
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onLeave(Event $event)
    {
        $this->logger->alert(sprintf(
            'Blog post (id: "%s") performed transaction "%s" from "%s" to "%s"',
            $event->getSubject()->getId(),
            $event->getTransition()->getName(),
            implode(', ', array_keys($event->getMarking()->getPlaces())),
            implode(', ', $event->getTransition()->getTos())
        ));
    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.blog_publishing.leave' => 'onLeave',
        );
    }
}
```

### Guard Events

Los eventos **workflow.guard** se notifican cada vez que se llama a cualquiera de los métodos **Workflow::can**, **Workflow::apply** o **Workflow::getEnabledTransitions**.

Con estos eventos se puede añadir programación personalizada para decidir qué transiciones son válidas o no.

En el siguiente ejemplo, se añade un evento de tipo Guard para evitar que los posts que no tengan título puedan cambiar al estado 'to_review'.

```php
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlogPostReviewListener implements EventSubscriberInterface
{
    public function guardReview(GuardEvent $event)
    {
        /** @var \App\Entity\BlogPost $post */
        $post = $event->getSubject();
        $title = $post->title;

        if (empty($title)) {
            // Posts with no title should not be allowed
            $event->setBlocked(true);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            'workflow.blogpost.guard.to_review' => array('guardReview'),
        );
    }
}
```

### Métodos del objeto Event

Cada evento del Workflow es una instacia de **Symfony\Component\Workflow\Event\Event**. Esto significa que cada evento tiene acceso a la siguiente información:

- getMarking()
Devuelve el **marking** del workflow.

- getSubject()
Devuelve el objeto/entidad sobre la que se está trabajando.

- getTransition()
Devuelve la transición que se está realizando.

- getWorkflowName()
Devuelve un string con el nombre del workflow.

### Métodos del objeto GuardEvent

Esta clase extiende de la anterior y tiene dos métodos más.

- isBlocked()
Devuelve true si la transición está bloqueada.

- setBlocked()
Bloquea la transición.

### Métodos del objeto TransitionEvent

Esta clase extiende de la primera y tiene dos métodos más.

- setNextState($state)
Establece el siguiente estado del objeto.

- getNextState()
Obtiene el nombre del siguiente estado.

## Configurar Guards en el yaml

Como alternativa a programar Guards, se puede definir en la configuración de cada transición una opción **guard**. El valor de esta opción es cualquier expresión válida creada con el componente **ExpressionLanguage**:

```yaml
# config/packages/workflow.yaml
framework:
    workflows:
        blog_publishing:
            # previous configuration
            transitions:
                to_review:
                    # the transition is allowed only if the current user has the ROLE_REVIEWER role.
                    guard: "is_granted('ROLE_REVIEWER')"
                    from: draft
                    to:   reviewed
                publish:
                    # or "is_anonymous", "is_remember_me", "is_fully_authenticated", "is_granted"
                    guard: "is_authenticated"
                    from: reviewed
                    to:   published
                reject:
                    # or any valid expression language with "subject" referring to the post
                    guard: "has_role("ROLE_ADMIN") and subject.isStatusReviewed()"
                    from: reviewed
                    to:   rejected
```

https://symfony.com/doc/master/components/expression_language.html

## Metadata (4.1)

En caso de que sea necesario, se pueden almacenar metadatos en los workflows, en los places y en las transitions con la opción **metadata**. Estos metadatos pueden tan simples como un string o tan complejos como necesitemos.

```php
# config/packages/workflow.yaml
framework:
    workflows:
        blog_publishing:
            metadata:
                title: 'Blog Publishing Workflow'
            # ...
            places:
                draft:
                    metadata:
                        max_num_of_words: 500
                # ...
            transitions:
                to_review:
                    from: draft
                    to:   review
                    metadata:
                        priority: 0.5
                # ...
```

En los controladores se puede acceder a los metadatos como se muestra en el siguiente código:

```php
public function myController(Registry $registry, Article $article)
{
    $workflow = $registry->get($article);

    $title = $workflow
        ->getMetadataStore()
        ->getWorkflowMetadata()['title'] ?? false
    ;

    // or
    $max_num_of_words = $workflow->getMetadataStore()
        ->getPlaceMetadata('draft')['max_num_of_words'] ?? false
    ;

    // or
    $aTransition = $workflow->getDefinition()->getTransitions()[0];
    $priority = $workflow
        ->getMetadataStore()
        ->getTransitionMetadata($aTransition)['priority'] ?? false
    ;
}
```

También existe una función genérica getMetadata() que sirve para los 3 casos según se ponga null, un string, o un objeto Transition:

```php
$title = $workflow->getMetadataStore()->getMetadata(null)['title'];
$max_num_of_words = $workflow->getMetadataStore()->getMetadata('draft')['max_num_of_words'];
$priority = $workflow->getMetadataStore()->getMetadata($aTransition)['priority'];
```

También se puede acceder a los metadatos en un Listener, a través del objeto Event.

```php
$timeLimit = $event->getMetadata('priority', $event->getTransition());
```

En Twig, los metadatos están disponibles a través de la función **workflow_metadata()**:

```html
<h2>Metadata</h2>
<p>
    <strong>Workflow</strong>:<br >
    <code>{{ workflow_metadata(article, 'title') }}</code>
</p>
<p>
    <strong>Current place(s)</strong>
    <ul>
        {% for place in workflow_marked_places(article) %}
            <li>
                {{ place }}:
                <code>{{ workflow_metadata(article, 'max_num_of_words', place) ?: 'Unlimited'}}</code>
            </li>
        {% endfor %}
    </ul>
</p>
<p>
    <strong>Enabled transition(s)</strong>
    <ul>
        {% for transition in workflow_transitions(article) %}
            <li>
                {{ transition.name }}:
                <code>{{ workflow_metadata(article, 'priority', transition) ?: '0' }}</code>
            </li>
        {% endfor %}
    </ul>
</p>
```

## Transition Blockers (4.1)

Los *transition blockers* permiten dar información acerca de por qué una transición no ha sido llevada a cabo:

```php
$event->addTransitionBlocker(
    new TransitionBlocker('You can not publish this article because it\'s too late. Try again tomorrow morning.')
);
```

A la información del transition blocker se puede acceder desde Twig.

```html
<h2>Why you can't transition?</h2>
<ul>
    {% for transition in workflow_all_transitions(article) %}
        {% if not workflow_can(article, transition.name) %}
            <li>
                <strong>{{ transition.name }}</strong>:
                <ul>
                {% for blocker in workflow_build_transition_blocker_list(article, transition.name) %}
                    <li>
                        {{ blocker.message }}
                        {% if blocker.parameters.expression is defined %}
                            <code>{{ blocker.parameters.expression }}</code>
                        {% endif %}
                    </li>
                {% endfor %}
                </ul>
            </li>
        {% endif %}
    {% endfor %}
</ul>
```

Normalmente los transition blockers se añaden en los eventos de tipo guard:

```php
namespace App\Listener\Workflow\Task;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;

class OverdueGuard implements EventSubscriberInterface
{
    public function guardPublish(GuardEvent $event)
    {
        $timeLimit = $event->getMetadata('time_limit', $event->getTransition());

        if (date('Hi') <= $timeLimit) {
            return;
        }

        $explanation = $event->getMetadata('explanation', $event->getTransition());
        $event->addTransitionBlocker(new TransitionBlocker($explanation , 0));
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.task.guard.done' => 'guardPublish',
        ];
    }
}
```

## Cómo depurar un Workflow

El componente de Workflow viene con un comando que genera una representación de los workflows en formato dot

> bin/console workflow:dump name | dot -Tpng -o graph.png

El comando dot forma parte de Graphviz y convierte ficheros con formato dot en ficheros png.

Se puede descargar de Graphviz.org.

A partir de symfony 4.1 se añade también el formato PlantUML.

> bin/console workflow:dump my_workflow | dot -Tpng > my_workflow.png

> bin/console workflow:dump my_workflow --dump-format=puml | java -jar plantuml.jar -p > my_workflow.png

https://symfony.com/doc/current/workflow/dumping-workflows.html

## Novedades en 4.3

### Se añade un contexto al método apply()

Al aplicar una transición, desde symfony 4.3 es posible pasar un contexto personalizado (por ejemplo, el usuario que ha realizado la transición, o la fecha actual):

```php
$workflow->apply($article, $request->request->get('transition'), [
    'time' => date('y-m-d H:i:s'),
]);
```

La función setMarking de nuestra entidad debe adaptarse a esta característica:

```php
 class Article
 {
-    public function setMarking($marking)
+    public function setMarking($marking, $context = [])
```

Así como la configuración del Workflow para utilizar **MethodMarkingStore**:

```yaml
 framework:
     workflows:
         article:
             type: workflow
             marking_store:
-                 type: multiple_state
+                 type: method
```

### Modificar el contexto desde un Listener

Como pasar el contexto en cada llamada a apply() es tedioso y genera código duplicado, existe la posibilidad también de hacerlo mediante un Listener:

```php
class TransitionEventSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onWorkflowArticleTransition(TransitionEvent $event)
    {
        $context = $event->getContext();

        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            $user = $token->getUser();
            if ($user instanceof UserInterface) {
                $context['user'] = $user->getUsername();
            }
        }

        $event->setContext($context);
    }

    public static function getSubscribedEvents()
    {
        return [
           TransitionEvent::class => 'onWorkflowArticleTransition',
        ];
    }
}
```

### Added color to dumped workflow

Desde la versión 4.3, es posible configurar algunos estilos del renderizado de un workflow mediante la opicón dump_style de los metadatos:

```yaml
transitions:
    submit:
        from: start
        to: travis
        metadata:
            title: transition submit title
            dump_style:
                label: 'My custom label'
                arrow_color: '#0088FF'
                label_color: 'Red'
```

### Allow to configure many initial places

Un workflow permite que el subject esté en más de un lugar simultáneamente. A partir de 4.3, la configuración admite multiples lugares iniciales:

```yaml
workflows:
    article:
        type: workflow
        initial_marking: [foo, bar]
        places: [foo, bar, a, b, c, d]
```

### Configuración más simple

Desde Symfony 4.3 se simplifica la configuración: Si el subject es single_state se indica simplemente con type: state_machine. En este caso la "property" será un string. Si el subject es multiple_state, se indica con type: workflow. En este caso, la property será un array.

```yaml
framework:
    workflows:
        article:
            type: workflow
            marking_store:
                type: method # This will be the default value in Symfony 5.0
                property: marking # This is the default value, it could be omitted
        task:
            type: state_machine
            marking_store:
                type: method # This will be the default value in Symfony 5.0
                property: state
```

### Nueva función de Twig workflow_transition_blockers()

Nueva función que devuelve la lista de Transition Blockers: **workflow_transition_blockers**

```html
<h2>Publication was blocked because:</h2>
<ul>
    {% for blocker in workflow_transition_blockers(article, 'publish') %}
        <li>
            {{ blocker.message }}
            {# Display the guard expression #}
            {% if blocker.parameters.expression is defined %}
                <code>{{ blocker.parameters.expression }}</code>
            {% endif %}
        </li>
    {% endfor %}
<ul>
```
