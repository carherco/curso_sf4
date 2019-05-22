# Tests Funcionales

Los tests funcionales no se diferencian demasiado de los tests unitarios en cuanto a PHPUnit se refiere. La diferencia es que se necesitan un flujo específico de trabajo:

- Realizar una petición
- Testear la respuesta
- Hacer click en un link, o en un botón, o enviar un formulario...
- Testear la respuesta
- ...

Para realizar tests funcionales necesesitamos algunas utilidades extra:

> composer require --dev symfony/browser-kit symfony/css-selector

Un ejemplo de test funcional podría ser el siguiente:

```php
// tests/Controller/PostControllerTest.php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testShowPost()
    {
        $client = static::createClient();

        $client->request('GET', '/post/hello-world');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
```

En este caso, las clases de test extienden de **WebTestCase**.

El método **createClient()** devuelve un cliente http que se comporta como un navegador y permite moverse por la aplicación:

```php
$crawler = $client->request('GET', '/post/hello-world');
```

El método **request()** devuelve un objeto **Crawler**que se puede utilizar para seleccionar elementos de la respuesta, hacer click en enlaces, enviar formularios...


El crawler utiliza selectores css para seleccionar elementos de la página:

```php
$this->assertGreaterThan(
    0,
    $crawler->filter('html:contains("Hello World")')->count()
);
```

También puede interaccionar con la página haciendo click en un enlace:

```php
$link = $crawler
    ->filter('a:contains("Ir a login")') 
    ->eq(1) // select the second link in the list
    ->link()
;

// and click it
$crawler = $client->click($link);
```

O enviando un formulario:

```php
$form = $crawler->selectButton('submit')->form();

$form['name'] = 'Lucas';
$form['form_name[subject]'] = 'Hola';

// submit the form
$crawler = $client->submit($form);
```


## Aserciones más utilizadas en tests funcionales

```php
use Symfony\Component\HttpFoundation\Response;

// ...

// asserts that there is at least one h2 tag
// with the class "subtitle"
$this->assertGreaterThan(
    0,
    $crawler->filter('h2.subtitle')->count()
);

// asserts that there are exactly 4 h2 tags on the page
$this->assertCount(4, $crawler->filter('h2'));

// asserts that the "Content-Type" header is "application/json"
$this->assertTrue(
    $client->getResponse()->headers->contains(
        'Content-Type',
        'application/json'
    ),
    'the "Content-Type" header is "application/json"' // optional message shown on failure
);

// asserts that the response content contains a string
$this->assertContains('foo', $client->getResponse()->getContent());
// ...or matches a regex
$this->assertRegExp('/foo(bar)?/', $client->getResponse()->getContent());

// asserts that the response status code is 2xx
$this->assertTrue($client->getResponse()->isSuccessful(), 'response status is 2xx');
// asserts that the response status code is 404
$this->assertTrue($client->getResponse()->isNotFound());
// asserts a specific 200 status code
$this->assertEquals(
    200, // or Symfony\Component\HttpFoundation\Response::HTTP_OK
    $client->getResponse()->getStatusCode()
);

// asserts that the response is a redirect to /demo/contact
$this->assertTrue(
    $client->getResponse()->isRedirect('/demo/contact')
    // if the redirection URL was generated as an absolute URL
    // $client->getResponse()->isRedirect('http://localhost/demo/contact')
);
// ...or simply check that the response is a redirect to any URL
$this->assertTrue($client->getResponse()->isRedirect());
```

## El Test Client

El objeto **client** simula un cliente HTTP similar a un navegador y permite hacer peticiones y acceder a la respuesta:

```php
$crawler = $client->request('GET', '/post/hello-world');
```

El método **request()** devuelve una instancia de un objeto **crawler**. La lista de argumentos que adminte el objeto request es la siguiente:


```php
request(
    $method,
    $uri,
    array $parameters = array(),
    array $files = array(),
    array $server = array(),
    $content = null,
    $changeHistory = true
)
```

El argumento de tipo array $server es el array de valores al que accedemos con la variable superglobal de PHP $\_SERVER.

```php
$client->request(
    'GET',
    '/post/hello-world',
    array(),
    array(),
    array(
        'CONTENT_TYPE'          => 'application/json',
        'HTTP_REFERER'          => '/foo/bar',
        'HTTP_X-Requested-With' => 'XMLHttpRequest',
    )
);
```


El crawler se utiliza para encontrar elementos del DOM en la respuesta. Estos elementos se pueden utilizar para hacer click en ellos, para enviar formularios, etc.


```php
$link = $crawler->selectLink('Go elsewhere...')->link();
$crawler = $client->click($link);

$form = $crawler->selectButton('validate')->form();
$crawler = $client->submit($form, array('name' => 'Fabien'));
```

Los métodos **click()** y **submit()** devuelven a su vez un objeto crawler.


```php
// hacer submit de un formulario (es más fácil con el objeto crawler)
$client->request('POST', '/submit', array('name' => 'Fabien'));

// submits a raw JSON string in the request body
$client->request(
    'POST',
    '/submit',
    array(),
    array(),
    array('CONTENT_TYPE' => 'application/json'),
    '{"name":"Fabien"}'
);

// Form submission with a file upload
use Symfony\Component\HttpFoundation\File\UploadedFile;

$photo = new UploadedFile(
    '/path/to/photo.jpg',
    'photo.jpg',
    'image/jpeg',
    123
);
$client->request(
    'POST',
    '/submit',
    array('name' => 'Fabien'),
    array('photo' => $photo)
);

// Perform a DELETE request and pass HTTP headers
$client->request(
    'DELETE',
    '/post/12',
    array(),
    array(),
    array('PHP_AUTH_USER' => 'username', 'PHP_AUTH_PW' => 'pa$$word')
);
```


También tiene métodos de navegación

```php
$client->back();
$client->forward();
$client->reload();

// clears all cookies and the history
$client->restart();
```

Acceso a objetos internos

```php
$history = $client->getHistory();
$cookieJar = $client->getCookieJar();

// the HttpKernel request instance
$request = $client->getRequest();

// the BrowserKit request instance
$request = $client->getInternalRequest();

// the HttpKernel response instance
$response = $client->getResponse();

// the BrowserKit response instance
$response = $client->getInternalResponse();

$crawler = $client->getCrawler();
```

Y aunque no es recomendable en tests funcionales, a veces necesitaremos acceder directamente a algún servicio.

```php
$container = $client->getContainer();
```

### Accessing the Profiler Data

En cada petición, se puede habilitar el profiler de symfony para recoger datos. Por ejemplo, podría servir para testear que una determinada página ejecuta menos de X consultas a la base de datos.

Pra habilitar el profiler en una petición hay que llamar al método **enableProfiler()** antes de realizar dicha petición.


```php
$client->enableProfiler();

$crawler = $client->request('GET', '/producto');

$profile = $client->getProfile();
```

### Redirecciones

Cuando una petición devuelve una respuesta de tipo redirección, el cliente de test no sigue la redirección automáticamente. Se puede examinar la respuesta y después forzar al cliente a que siga la redirección con el método **followRedirect()**.

```php
$crawler = $client->followRedirect();
```

También existe el método **followRedirects()** (en plural) para forzar al cliente a que siga automáticamente todas las redirecciones. Se utilizaría antes de realizar la petición.

```php
$client->followRedirects();
```

Si se le pasa *false* a este método, el cliente dejaría de seguir las redirecciones.

```php
$client->followRedirects(false);
```

## El objeto Crawler

Cada vez que realizamos una petición con el objeto client, nos devuelven una instancia de Crawler.

El crawler nos permite analizar y acceder a los elementos del DOM de la respuesta.

### Acceso a elementos del DOM

De forma similar a jQuery, el crawler tiene métodos para acceder a elementos del DOM. 

En el siguiente ejemplo, el crawler accede a todos los elementos input[type=submit], selecciona el último de ellos, y selecciona posteriormente al elemento padre de dicho elemento.

```php
$newCrawler = $crawler->filter('input[type=submit]')
    ->last()
    ->parents()
    ->first()
;
```

Hay disponibles muchos métodos para acceder a los elementos del DOM:

- filter('h1.title')
Devuelve elementos en base a un selector CSS.

- filterXpath('h1')
Devuelve elementos en base a una expresión XPath.

- eq(1)
Devuelve el elemento que ocupa la posición dada.

- first()
Devuelve el primer elemento.

- last()
Devuelve el último elemento.

- siblings()
Todos los elementos "hermanos".

- nextAll()
Todos los hermanos siguientes.

- previousAll()
Todos los hermanos anteriores.

- parents()
Padres.

- children()
Hijos.

- reduce(callable)
Elementos para los cual el *callable* no devuelve *false*.

Todos los métodos anteriores devuelven una instancia de crawler, por lo que se pueden encadenar.


```php
$crawler
    ->filter('h1')
    ->reduce(function ($node, $i) {
        if (!$node->getAttribute('class')) {
            return false;
        }
    })
    ->first()
;
```

Existe otro método **count()** que no devuelve un crawler sino que devuelve el número de elementos del crawler.

### Extracción de información

```php
// devuelve el valor del atributo dado del primer elemento
$crawler->attr('class');

// devuelve el valor del primer elemento
$crawler->text();

// extrae un array de atributos para todos los nodos
// devuelve un array por cada elemento del crawler con el class y el href
$info = $crawler->extract(array('class', 'href'));

// ejecuta un callable para cada nodo y devuelve un array con los resultados
$data = $crawler->each(function ($node, $i) {
    return $node->attr('href');
});
```

### Enlaces

Para seleccionar un enlace, podemos utilizar las funciones de acceso anteriores o un método especial **selectLink()**.

```php
$crawler->selectLink('Click here');
```

Este método selecciona todos los enlaces que contengan el texto dato o imágenes clickables cuyo atributo *alt* contenga dicho texto. Este método devuelve otro objeto crawler.

Esos elementos tienen un objeto especial, que se puede obtener con el método **link()**.

```php
$link = $crawler->selectLink('Click here')->link();
```

Este objeto especial tiene métodos **getMethod()** y **getUri()**.

```php
$method = $link->getMethod();
$url = $link->getUri();
```

Para hacer click en el enlace, se utiliza el método **click()** del client pasándole el objeto sobre el que se quiere hacer click.

```php
$link = $crawler->selectLink('Click here')->link();

$client->click($link);
```

### Forms

La forma de seleccionar un formulario con el crawler es un tanto curiosa.

Los formularios se pueden seleccionar utilizando sus botones, que a su vez se pueden seleccionar con el método **selectButton()** de forma similar a los enlaces:

```php
$buttonCrawlerNode = $crawler->selectButton('submit');
```

Esta hecho así porque un formulario puede tener varios botones, pero un botón solamente puede pertenecer a un formulario.

El método selectButton() busca por los siguientes conceptos: 

- El valor del atributo value;
- El valor del atribuot id o del atributo alt para imágenes.
- El valor del atributo id o del atributo name para etiquetas button.

Una vez tenemos un crawler que representa a un botón, podemos llamar al método **form()** para obtener una isntacia del objeto Form asociado:

```php
$form = $buttonCrawlerNode->form();
```

Al llamar al método form(), podemos pasar un array de valores para los campos del formulario, que sobreescribiran a los valores que hubiera por defecto:

```php
$form = $buttonCrawlerNode->form(array(
    'name'              => 'Carlos',
    'my_form[subject]'  => 'Este curso mola',
));
```

El segundo argumento del método form es para pasar un método HTTP específico:

```php
$form = $buttonCrawlerNode->form(array(), 'DELETE');
```

El objeto client puede hacer submit de instancias de objetos Form a través de su método **submit()**.

```php
$client->submit($form);
```

Al método submit también pueden pasársele los valores de los campos como segundo argumento:

```php
$client->submit($form, array(
    'name'              => 'Carlos',
    'my_form[subject]'  => 'Este curso mola',
));
```

Y siempre podemos utilizar el objeto Form como si fuera un array para establecer los valores de los campos que queramos:

También se pueden establecer los valores del formulario directamente a través de la variable $form como si fuera un array:

```php
$form['name'] = 'Carlos';
$form['my_form[subject]'] = 'Este curso mola';
```

Según cada campo, tenemos también algunos otros métodos:


```php
// selects an option or a radio
$form['country']->select('France');

// ticks a checkbox
$form['like_symfony']->tick();

// uploads a file
$form['photo']->upload('/path/to/lucas.jpg');
```

Por último, el objeto Form tiene un método **getValues()** y un método getFiles() que devuelven los valores de los campos y de los archivos. Los dos métodos tienen una versión que devuelve los valores en formato php: **getPhpValues()** y **getPHPFiles()**.

#### Añadir o quitar elementos a un Form

Añadir campos:

```php
// obtiene el form
$form = $crawler->filter('button')->form();

// obtiene the valores
$values = $form->getPhpValues();

// añade campos al array $values
$values['task']['tags'][0]['name'] = 'foo';
$values['task']['tags'][1]['name'] = 'bar';

// Hace submit del formulario
$crawler = $client->request($form->getMethod(), $form->getUri(), $values,  $form->getPhpFiles());
```

Eliminar campos:

```php
$form = $crawler->filter('button')->form();
$values = $form->getPhpValues();

unset($values['task']['tags'][0]);

$crawler = $client->request($form->getMethod(), $form->getUri(), $values, $form->getPhpFiles());
```

## Novedades en symfony 4.2

### Nuevo método clickLink()

Han incorporado a la librería un nuevo método que hace más sencillo hacer click en un enlace.

Lo que antes había que hacer así:

```php
$client->request('GET', '/');
$link = $crawler->selectLink('Login')->link();
$crawler = $client->click($link);
```

Ahora se puede abrebiar así:

```php
$client->request('GET', '/');
$crawler = $client->clickLink('Login');
```

### Nuevo método submitForm()

De igual forma, han incorporado el método submitForm para hacer más sencillo el envío de formularios.

Lo que antes había que hacer así:

```php
$client->request('GET', '/register');
$form = $crawler->selectButton('Sign Up')->form();
$crawler = $client->submit($form, [
    'name' => 'Jane Doe',
    'username' => 'jane',
    'password' => 'my safe password',
]);
```

Ahora se puede abrebiar así:

```php
$client->request('GET', '/register');
$crawler = $client->submitForm('Sign Up', [
    'name' => 'Jane Doe',
    'username' => 'jane',
    'password' => 'my safe password',
]);
```

https://symfony.com/blog/new-in-symfony-4-3-domcrawler-improvements