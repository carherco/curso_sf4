# Routing

## Resumen básico

En symfony, una ruta es un mapeo entre una url y un controlador. Las rutas no se programan, sino que se configuran o definen.

Hay 4 formas distintas de configurar las rutas en symfony:

- Con anotaciones en los propios controladores
- En un fichero de configuración YML
- En un fichero de configuración XML
- En un fichero de configuración PHP

**El orden de las rutas importa**. La primera ruta que haga *match* con la petición, será la ruta escogida por symfony.

Si no conoces cómo configurar rutas básicas o rutas con parámetros, puedes acudir a la documentación oficial de symfony:

https://symfony.com/doc/current/routing.html

## Restricciones de los parámetros (requirements)

```yml
user_edit:
  path:  /user/edit/{id}
  defaults: { _controller: App\Controller\UsersController:edit }
  requirements:
    id: '\d+'
```

El \d+ es una expresión regular. 

A partir de la versión 4.1, se acepta el siguiente formato:

```php
/**
 * @Route("/blog/{page<\d+>}", name="blog_list")
 */
public function list($page)
{
    // ...
}
```

Otro ejemplo con expresiones regulares podría ser:

```yml
homepage:
    path:      /{_locale}
    defaults:  { _controller: App\Controller\MainController:homepage }
    requirements:
        _locale:  es|fr
```

## Valor por defecto de un parámetro

```yml
user_edit:
  path:  /user/edit/{id}
  defaults: { _controller: App\Controller\UsersController:edit, id: 1 }
  requirements:
    id: '\d+'

homepage:
    path:      /{_locale}
    defaults:  { _controller: App\Controller\MainController:homepage, _locale: en }
    requirements:
        _locale:  en|fr
```

Aquí podemos ver unos ejemplos:

```
/     =>  {_locale} = "en"
/en   =>  {_locale} = "en"
/fr   =>  {_locale} = "fr"
/es   =>  No hace match
```

Con anotaciones:

```php
class MainController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "en"}, requirements={
     *     "_locale": "en|fr"
     * })
     */
    public function homepageAction($_locale)
    {
    }
```

Sin hacer nada más, symfony aplicará automáticamente el idioma correspondiente.

Desde la versión 4.1, se admite esta sintaxis

```php
/**
* @Route("/blog/{page<\d+>?1}", name="blog_list")
*/
public function list($page)
{
    // ...
}
```

## Parámetros extra

Es posible pasar parámetros extra en la ruta a través de la opción *defaults*:

```yml
# config/routes.yaml
blog:
    path:       /usuarios/{page}
    controller: App\Controller\UsuariosController:index
    defaults:
        page: 1
        title: "Listado de usuarios"
```

## Parámetros especiales

El componente de routing tiene estos 4 parámetros especiales:

- **_controller**: Este parámetro determina qué controlador se ejecutará. La sintaxis es bundle:controller:action

- **_locale**: Establece el idioma de la petición

- **_format**: Establece el formato de la request (Ej: Content-Type:application/json).

- **_fragment**: Establece el *fragment* de la url

Ejemplo:

```yml
article_show:
  path:     /articles/{slug}.{_format}
  defaults: { _controller: App\Controller\Article:show, _format: html }
  requirements:
      _format:  html|rss
```

```php
class ArticleController extends Controller
{
    /**
     * @Route(
     *     "/articles/{slug}.{_format}",
     *     defaults={"_format": "html"},
     *     requirements={
     *         "_format": "html|rss",
     *     }
     * )
     */
    public function showAction($_locale, $year, $slug)
    {
    }
}
```

## Localized Routing (i18n)

```php
/**
 * @Route({
 *     "nl": "/over-ons",
 *     "en": "/about-us"
 * }, name="about_us")
 */
public function about()
{
    // ...
}
```

```yaml
about_us:
    path:
        nl: /over-ons
        en: /about-us
    controller: App\Controller\CompanyController::about
```

Cuando una ruta hace match, el _locale se establece automáticamente.

Muchas veces, las rutas de nuestra aplicación van prefijadas con el _locale. Esto se puede configurar de la siguiente forma:

```yaml
controllers:
    resource: '../../src/Controller/'
    type: annotation
    prefix:
        en: '' # don't prefix URLs for English, the default locale
        nl: '/nl'
```

## Redirecciones

### Redireccionar a otra url

```yml
# redirecting the homepage
homepage:
    path: /
    controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction
    defaults:
        path: /app
        permanent: true
```

### Redireccionar a otra ruta

```yml
admin:
    path: /wp-admin
    controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::redirectAction
    defaults:
        route: sonata_admin_dashboard
        permanent: true
```

### Mantener los query params en la redirección y el Request Method

A partir de la versión 4.1 podemos indicar que queremos manterer los query params y/o el método de la petición

```yml
# redirecting the homepage
homepage:
    path: /
    controller: Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction
    defaults:
        path: /app
        permanent: true
        keepQueryParams: true
        keepRequestMethod: true
```

## Depuración de rutas

Listado de las todas las rutas:

> php bin/console debug:router

Información sobre una ruta específica:

> php bin/console debug:router article_show

Testear una URL:

> php bin/console router:match /blog/my-latest-post

## Restricción de rutas por Host

```yml
# config/routes.yaml
mobile_homepage:
    path:       /
    host:       m.example.com
    controller: App\Controller\MainController::mobileHomepage

homepage:
    path:       /
    controller: App\Controller\MainController::homepage
```

```php
// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    /**
     * @Route("/", name="mobile_homepage", host="m.example.com")
     */
    public function mobileHomepage()
    {
        // ...
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        // ...
    }
}
```

Se pueden utilizar parámetros en la sección *host*:

```yml
# config/routes.yaml
projects_homepage:
    path:       /
    host:       "%project_name%.example.com"
    controller: App\Controller\MainController::projectsHomepage
homepage:
    path:       /
    controller: App\Controller\MainController::homepage
contact:
    path:       /{_locale}/contact
    controller: App\Controller\MainController::contact
    requirements:
        _locale: '%app.locales%'
with_prefix:
    path:       /%app.route_prefix%/contact
    controller: App\Controller\MainController::contact
```

```yaml
parameters:
    project.name: myproject
    app.locales: en|es
    app.route_prefix: af
```

Si se usa algún parámetro, puede tener valor por defecto, restricciones, etc.

## Restricción de rutas por método

```yml
api_user_show:
    path:     /api/users/{id}
    defaults: { _controller: App\Controller\UsersApi:show }
    methods:  [GET, HEAD]

api_user_edit:
    path:     /api/users/{id}
    defaults: { _controller: App\Controller\UsersApi:edit }
    methods:  [PUT]
```

```php
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
// ...

class UsersApiController extends Controller
{
    /**
     * @Route("/api/users/{id}")
     * @Method({"GET","HEAD"})
     */
    public function showAction($id)
    {
        // ... return a JSON response with the post
    }

    /**
     * @Route("/api/users/{id}")
     * @Method("PUT")
     */
    public function editAction($id)
    {
        // ... edit a post
    }
}
```

## Restricción de rutas por protocolo

```yml
# config/routes.yaml
secure:
    path:       /secure
    controller: App\Controller\MainController::secure
    schemes:    [https]
```

```php
// src/Controller/MainController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MainController extends Controller
{
    /**
     * @Route("/secure", name="secure", schemes={"https"})
     */
    public function secure()
    {
        // ...
    }
}
```

(se puede gestionar también con el componente de seguridad de symfony)

https://symfony.com/doc/current/security/force_https.html

## Novedades en Symfony 4.1 y 4.2

### Importación de rutas con prefijo

Se ha introducido la opción **name_prefix** para cargar rutas de otro fichero poniéndoles un prefijo:

```yml
...

api:
    resource: ../controller/routing.yaml
    # this prefix is added to all the action route names
    name_prefix: api_
    # this prefix is added to all the action URLs
    prefix: /api
```

Esto permite cargar un fichero de rutas varias veces o configurar el prefijo para las rutas de un bundle.

### Redirecciones 307 y 308

Las redirecciones 301 y 302 transforman las peticiones POST en peticiones GET. Para resolver ese problema, se introdujeron dos nuevos códigos HTTP en el estándar: el 307 y el 308. 

En la versión 4.1 de Symfony se han adaptado a estos nuevos códigos con la opción **keepRequestMethod**:

```yml
route_301:
    # ...
    defaults:
        # ...
        permanent: true

route_302:
    # ...
    defaults:
        # ...
        permanent: false

route_307:
    # ...
    defaults:
        # ...
        permanent: false
        keepRequestMethod: true

route_308:
    # ...
    defaults:
        # ...
        permanent: true
        keepRequestMethod: true
```

### Rutas I18n

A partir de 4.1 es posible definir distintos paths para una misma ruta dependiendo el idioma:

```yml
contact:
    controller: App\Controller\ContactController::send
    path:
        en: /send-us-an-email
        nl: /stuur-ons-een-email
```

En la versión con anotaciones sería:

```php
use Symfony\Component\Routing\Annotation\Route;

class ContactController
{
    /**
     * @Route({
     *     "en": "/send-us-an-email",
     *     "nl": "/stuur-ons-een-email"
     * }, name="contact")
     */
    public function send()
    {
        // ...
    }
}
```

La generación de la ruta cogería el locale que se esté utilizando en ese momento, aunque también podemos elegir un locale concreto

```php
// uses the current request locale
$url = $urlGenerator->generate('contact');

// ignores the current request locale and generates '/stuur-ons-een-email'
$url = $urlGenerator->generate('contact', ['_locale' => 'nl']);
```

También se pueden utilizar con prefijos:

```yml
# config/routes/annotations.yaml
site:
    resource: '../src/Controller/'
    type: annotation
    prefix:
        en: '/site'
        es: '/sitio'
```