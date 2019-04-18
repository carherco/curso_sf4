# Seguridad

El sistema de seguridad de symfony es muy potente, pero también puede llegar a ser muy confuso.

Para instalar el componente de seguridad con Symfony Flex, en caso de no tenerlo ya instalado, hay que ejecutar

>  composer require security


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