# Seguridad

El sistema de seguridad de symfony es muy potente, pero también puede llegar a ser muy confuso.

Para instalar el componente de seguridad con Symfony Flex, en caso de no tenerlo ya instalado, hay que ejecutar

> composer require security

La seguridad se configura en el archivo security.yml. Por defecto, tiene el siguiente aspecto:

```yml
# app/config/security.yml
security:
    providers:
        in_memory:
            memory: ~

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            anonymous: ~
```

Referencia del fichero de configuración security.yaml:
https://symfony.com/doc/current/reference/configuration/security.html

Hay que distinguir dos conceptos de seguridad: Autenticación y Autorización.

## Autenticación

Vamos a empezar con un ejemplo básico de seguridad: Vamos a limitar el acceso a algunas páginas de nuestra aplicación y a pedir autenticación básica HTTP.

```yml
security:
    providers:
        in_memory:
            memory: ~

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        main:
            anonymous: ~
            http_basic: ~

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/alumno, roles: ROLE_ADMIN }
        - { path: ^/asignatura, roles: ROLE_USER }
```

### Access Control

En la sección access_control, restringimos el acceso a determinadas rutas de forma que únicamente puedan acceder aquellos usuarios con un rol determinado.

En el ejemplo anterior, solamente los usuarios con rol ROLE_ADMIN pueden acceder a las rutas que empiezan por /admin, /alumno y /asignatura.

### Providers

En la sección providers, configuramos el sistema o sistemas proveedores de usuarios. Vamos a ver un ejemplo sencillo del proveedor *in_memory*.

```yml
    providers:
        in_memory:
            memory:
                users:
                    carlos:
                        password: pass
                        roles: 'ROLE_USER'
                    admin:
                        password: word
                        roles: 'ROLE_ADMIN'
```

### Encoders

```yml
    encoders:
        # Examples:
        App\Entity\User1: sha512
        App\Entity\User2:
            algorithm:           sha512
            encode_as_base64:    true
            iterations:          5000

        # PBKDF2 encoder
        # see the note about PBKDF2 below for details on security and speed
        App\Entity\User3:
            algorithm:            pbkdf2
            hash_algorithm:       sha512
            encode_as_base64:     true
            iterations:           1000
            key_length:           40

        # Example options/values for what a custom encoder might look like
        App\Entity\User4:
            id:                   App\Security\MyPasswordEncoder

        # BCrypt encoder
        # see the note about bcrypt below for details on specific dependencies
        App\Entity\User5:
            algorithm:            bcrypt
            cost:                 13

        # Plaintext encoder
        # it does not do any encoding
        App\Entity\User6:
            algorithm:            plaintext
            ignore_case:          false

        # Argon2i encoder
        Acme\DemoBundle\Entity\User6:
            algorithm:            argon2i
```

### Entity Provider

Vamos ahora a cambiar el provider in_memory por uno de base de datos:

Lo primero que necesitamos es nuestra entidad de usuarios

```php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        // si necesitáramos un "salt" podríamos hacer algo así
        // $this->salt = md5(uniqid('', true));
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // El método es necesario aunque no utilicemos un "salt"
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
        ) = unserialize($serialized);
    }
}
```

Una clase *User* debe implementar las interfaces UserInterface y Serializable.

Como consecuencia de implementar la interfaz UserInterface tenemos que crear los
siguientes métodos:

- getRoles()
- getPassword()
- getSalt()
- getUsername()
- eraseCredentials()

Y como consecuencia de implementar Serializable, tenemos que crear los siguientes
métodos:

- serialize()
- unserialize()

Al final de cada petición el objeto User es serializado y metido en la sesión. En la siguiente petición, se deserializa. Symfony hace dichas operaciones llamando a los métodos serialize() y unserialize().

Ya solamente queda configurar el security.yml para que utilice un provider basado
en nuestra entidad

```yml
# app/config/security.yml
security:
    encoders:
        App\Entity\User:
            algorithm: bcrypt

    # ...

    providers:
        mi_poveedor:
            entity:
                class: App:User
                property: username

    firewalls:
        main:
            pattern:    ^/
            http_basic: ~
            provider: mi_poveedor

    # ...
```

### AdvancedUserInterface

En vez de extender de UserInterface, podemos extender de AdvancedUserInterface.
Para ello tenemos que definir los siguientes métodos:

```php
// src/AppBundle/Entity/User.php

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
// ...

class User implements AdvancedUserInterface, \Serializable
{
    // ...

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    // serialize and unserialize must be updated - see below
    public function serialize()
    {
        return serialize(array(
            // ...
            $this->isActive
        ));
    }
    public function unserialize($serialized)
    {
        list (
            // ...
            $this->isActive
        ) = unserialize($serialized);
    }
}
```


- isAccountNonExpired(): comprueba si la cuenta de usuario ha caducado
- isAccountNonLocked(): comprueba si el usuario está bloquedado
- isCredentialsNonExpired() comprueba si la contraseña ha caducado;
- isEnabled() comprueba si el usuario está habilitado.

Si cualquiera de estos métodos devuelve false, el usuario no podrá hacer login.

Según cuál de estos métodos devuelva falso, Symfony generará un mensaje diferente.

### Configurar múltiples providers

Es posible configurar múltiples providers. En caso de que un firewall no especifique qué provider va a utilizar, utilizará el primero de ellos.

```yml
# app/config/security.yml
security:
    providers:
        chain_provider:
            chain:
                providers: [in_memory, user_db]
        in_memory:
            memory:
                users:
                    foo: { password: test }
        user_db:
            entity: { class: AppBundle\Entity\User, property: username }

    firewalls:
        secured_area:
            # ...
            pattern: ^/
            provider: user_db
            form_login: ~
```

Symfony dispone de 4 providers ya programados:

- memory
- entity
- ldap
- chain

El provider *chain* no es un provider en sí, sino que sirve para especificar una cadena de providers.

### Autenticación con formulario de login

Vamos a cambiar ahora el método de login http_basic por un formulario de login.

```yml
# app/config/security.yml
security:
    # ...

    firewalls:
        main:
            anonymous: ~
            form_login:
                login_path: login
                check_path: login
                default_target_path: after_login_route_name
                always_use_default_target_path: true
```

Con esto decimos a symfony que vamos a utilizar un formulario de login, y que rediriga a la ruta de nombre "login" cuando sea necesario identificar a un usuario.

Los siguiente es crear una acción y una plantilla para esa ruta:

```php
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

public function loginAction(Request $request, AuthenticationUtils $authUtils)
{
    // get the login error if there is one
    $error = $authUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authUtils->getLastUsername();

    return $this->render('security/login.html.twig', array(
        'last_username' => $lastUsername,
        'error'         => $error,
    ));
}
```

```yml
{% if error %}
    <div>{{ error.messageData }}</div>
{% endif %}

<form action="{{ path('login') }}" method="post">
    <label for="username">Usuario:</label>
    <input type="text" id="username" name="_username" value="{{ last_username }}" />

    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="_password" />

    {#
        Si queremos controlar la url a la que se redirigirá el usuario después de hacer login
        <input type="hidden" name="_target_path" value="/account" />
    #}

    <button type="submit">Login</button>
</form>
```

### JSON login

```yml
security:
    # ...

    firewalls:
        main:
            anonymous: ~
            json_login:
                check_path: /login
```

```php
// src/Controller/SecurityController.php

// ...
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request)
    {
        $user = $this->getUser();

        return $this->json(array(
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ));
    }
}
```

Cuando se haga un POST a la url /login con el header *Content-Type: application/json* con el siguiente body:

```json
{
    "username": "dunglas",
    "password": "MyPassword"
}
```

El sistema de seguridad interceptará la petición e iniciará el proceso de autenticación.

Symfony realiza la autenticación del usuario según la configuración establecida lanzando un error si el proceso falla. Si la autenticación es correcta, entonces se ejecuta el controlador definido anteriormente.

Si el json tiene una estructura diferente:

```json
{
    "security": {
        "credentials": {
            "login": "dunglas",
            "password": "MyPassword"
        }
    }
}
```

La configuración será de la siguiente forma

```yml
# config/packages/security.yaml
security:
    # ...

    firewalls:
        main:
            anonymous: ~
            json_login:
                check_path:    login
                username_path: security.credentials.login
                password_path: security.credentials.password
```

https://symfony.com/doc/current/security/json_login_setup.html


En las nuevas versiones de Symfony, el servicio de seguridad se llama ahora Security.

```php
// src/AppBundle/Newsletter/NewsletterManager.php

// ...
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class NewsletterManager
{
    protected $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function sendNewsletter()
    {
        if (!$this->security->isGranted('ROLE_NEWSLETTER_ADMIN')) {
            throw new AccessDeniedException();
        }

        // ...
    }

    // ...
}
```

## Los comandos make:auth y make:user

En symfony 4.1 han añadido un comando nuevo **make:auth** que nos asiste en la configuración de la seguridad de nuestra aplicación.

Este comando es interactivo: nos va haciendo preguntas y al terminar nos genera el código y configuración necesarios.

> php bin/console make:auth

Previamente, conviene haber creado una entidad User bien manualmente o bien con el también nuevo comando **make:user**.

> php bin/console make:user

Enlace a la documentación oficial: https://symfony.com/doc/current/security/form_login_setup.html

## Roles

Al hacer login, el usuario recibe un conjunto específico de roles (por ejemplo: ROLE_ADMIN).

Los roles deben empezar con el prefijo *ROLE_*.

Un usuario autenticado debe de tener al menos un rol.

Es posible establecer una jerarquía de roles:

```yml
security:
    # ...

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
```

### Los pseudo-roles

Symfony tiene 3 pseudo-roles (atributos), que no son roles, pero actúan como si lo fueran:

- IS_AUTHENTICATED_ANONYMOUSLY: Todos los usuarios tienen este atributo, estén logeados o no
- IS_AUTHENTICATED_REMEMBERED: Todos los usuarios logeados tienen este atributo
- IS_AUTHENTICATED_FULLY: Todos los usuarios logeados excepto los que están logeados a través de una "remember me cookie".

## Autorización

El proceso de autorización consiste en añadir código para que un recurso requiera un *atributo* específico (un rol u otro tipo de atributo) para acceder a dicho recurso.

Añadir código para denegar el acceso a un recurso puede hacerse bien mediante la sección *access_control* del security.yml o bien a través del servicio *security.authorization_checker*.

### Access control

En el access control, además de la url, se puede configurar accesos por IP, host name o métodos HTTP.

También se puede utilizar la sección *access_control* para redireccionar al usuario al protocolo *https*

Ejemplos:

```yml
security:
    # ...
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN, ip: 127.0.0.1 }
        - { path: ^/admin, roles: ROLE_ADMIN, host: symfony\.com$ }
        - { path: ^/admin, roles: ROLE_ADMIN, methods: [POST, PUT] }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

Primero Symfony búsca el match correspondiente según las coincidencias de:

- path
- ip
- host
- methods

Una vez vista cuál es la coincidencia, permite o deniega el acceso, según se cumplan las condiciones de:
  
- roles: si el usuario no tiene este rol, se le deniega el acceso
- allow_if: si la expresión evaluada devuelve *false* se le deniega el acceso

```yml
security:
    # ...
    access_control:
        -
            path: ^/_internal/secure
            allow_if: "'127.0.0.1' == request.getClientIp() or has_role('ROLE_ADMIN')"
```

- requires_channel: si el protocolo (canal) de la petición no coincide con el indicado, se le redirige al indicado.

```yml
security:
    # ...
    access_control:
        - { path: ^/cart/checkout, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https }
```

Si el acceso resulta denegado y el usuario no está autenticado, se le redirige al sistema de autenticación cofigurado.

Si el acceso resulta denegado y ya estaba autenticado, se le muestra una página de 403 acceso denegado.

### El servicio Authorization_checker

La forma de añadir código de denegación de acceso a través del servicio *security.authorization_checker* son las siguientes:

A) En los controladores:

```php
public function helloAction($name)
{
    // The second parameter is used to specify on what object the role is tested.
    $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

    // Old way :
    // if (false === $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
    //     throw $this->createAccessDeniedException('Unable to access this page!');
    // }

    // ...
}
```

Gracias al bundle SensioFrameworkExtraBundle, se puede hacer lo mismo con anotaciones:

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 */
public function helloAction($name)
{
    // ...
}
```

Incluso se puede poner a nivel de la clase controladora

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("/asignaturas")
 */
class AsignaturasController extends Controller
{
  
}
```

B) En las plantillas

```yml
{% if is_granted('ROLE_ADMIN') %}
    <a href="...">Delete</a>
{% endif %}
```

C) En los servicios

```php
// src/AppBundle/Newsletter/NewsletterManager.php

// ...
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NewsletterManager
{
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function sendNewsletter()
    {
        if (!$this->authorizationChecker->isGranted('ROLE_NEWSLETTER_ADMIN')) {
            throw new AccessDeniedException();
        }

        // ...
    }

    // ...
}
```

## Acceso al objeto User

Tras la autenticación, el objeto User asociado al usuario actual se puede obtener a través del servicio *security.token_storage*.

En un controlador, podemos tener acceso fácilmente al objeto User gracias a la inyección de dependencias.

```php
use Symfony\Component\Security\Core\User\UserInterface;

public function index(UserInterface $user)
{
    //...
}
```

De qué tipo de clase sea nuestro objeto *$user* dependerá de nuestro *user provider*.

Si nuestra clase controladora extiende de Controller se puede acceder también al usuario con $this->getUser().

```php
use Symfony\Component\Security\Core\User\UserInterface;

public function index()
{
    $user = $this->getUser();

    //...
}
```

En twig, podemos acceder al objeto user con app.user

```twig
{% if is_granted('IS_AUTHENTICATED_FULLY') %}
    <p>Username: {{ app.user.username }}</p>
{% endif %}
```

## Las anotaciones @IsGranted y @Security

Las anotaciones @IsGranted y @Security restringen el acceso a los controladores:

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PostController extends Controller
{
    /**
     * @IsGranted("ROLE_ADMIN")
     *
     * or use @Security for more flexibility:
     *
     * @Security("is_granted('ROLE_ADMIN') and is_granted('ROLE_FRIENDLY_USER')")
     */
    public function indexAction()
    {
        // ...
    }
}
```

### @IsGranted

La anotación @IsGranted() es muy simple de utilizar. Se utiliza para restringir roles o para restricciones basadas en Voters:

```php
/**
 * @Route("/posts/{id}")
 *
 * @IsGranted("ROLE_ALUMNO")
 * @IsGranted("nota_ver", subject="nota")
 */
public function verAction(Nota $nota)
{
}
```

Para acceder a una acción hay que pasar todas las restricciones.

La anotación @IsGranted permite también personalizar el statusCode y el mensaje de error.

```php
/**
 * Will throw a normal AccessDeniedException:
 *
 * @IsGranted("ROLE_ADMIN", message="No access! Get out!")
 *
 * Will throw an HttpException with a 404 status code:
 *
 * @IsGranted("ROLE_ADMIN", statusCode=404, message="Post not found")
 */
public function indexAction(Post $post)
{
}
```

### @Security

La anotación @Security es más flexible que @IsGranted: permite crear expresiones con lógica personalizada:

```php
/**
 * @Security("is_granted('ROLE_ALUMNO') and is_granted('ver', nota)")
 */
public function verAction(Nota $nota)
{
    // ...
}
```

Las expresiones pueden utilizar todas las funciones admitidas en la sección access_control del security.yaml, además de la función is_granted().

Las expresiones tienen acceso a las siguientes variables:

- token: El token de seguridad actual;
- user: El objeto usuario actual;
- request: La instancia de la request;
- roles: Los roles del usurio;
- y todos los atributos de la request.

Se puede lanzar una excepción Symfony\Component\HttpKernel\Exception\HttpException exception en vez de una excepción Symfony\Component\Security\Core\Exception\AccessDeniedException using utilizando el statusCode 404:

```php
/**
 * @Security("is_granted('ver', nota)", statusCode=404)
 */
public function verAction(Nota $nota)
{
}
```

También se puede personalizar el mensaje de error:

```php
/**
 * @Security("is_granted('ver', nota)", statusCode=404, message="Resource not found.")
 */
public function verAction(Nota $nota)
{
}
```

Las anotaciones @IsGranted y @Security se pueden utilizar a nivel de clase para proteger todas las acciones de la clase controladora.

