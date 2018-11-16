# Ejercicios

## Subscriber

### Enunciado

Imaginemos que nuestra aplicación symfony consiste únicamente de una API REST.

Tenemos también un servicio TokenValidatorService con un método validate($token) que nos devuelve un booleano indicando si un token es válido o no para acceder a la API.

En nuestro services.yaml tenemos activados autoconfigure y autowire.

Crear y configurar un subscriber que lance un AccessDeniedHttpException si se realiza una petición a cualquier url de la API con un token que no sea válido.

NO es necesario programar el servicio TokenValidatorService.

## Listener

### Enunciado

Igual que el ejercicio anterior pero con un listener en vez de con un subscriber.

NO es necesario programar el servicio TokenValidatorService.



Ejercicio routing 4.1
---------------------

Se necesita crear una acción para mostrar los artículos de un blog. 

- La acción en concreto mostrará el artículo en concreto en el idioma que venga en la url. Solamente se contemplan los idiomas español (es), inglés (es) y francés (fr).
- La misma acción devolverá el contenido del artículo en formato html o en formato rss según la extensión indicada en la ruta. El formato por defecto, si no se indica extensión, será html
- Para compartir por redes sociales, por SEO, para tener rutas amigables, etc, tanto el idioma como el título del artículo estarán incluidos en la ruta:


Ejemplos de urls posibles: 

- /articles/es/2010/mi-post
- /articles/en/2010/my-post.rss
- /articles/es/2013/mi-otro-post.html



Ejercicio routing 4.2:
--------------

Crear la ruta correspondiente a esta acción para que redireccione TODAS las URLs acabadas en '/' a la misma URL sin la '/'.


```php
    public function removeTrailingSlash(Request $request, $url)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url_nueva = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        // 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
        // that it does not allow changing the request method (e.g. from POST to GET)
        return $this->redirect($url_nueva, 308);
    }
```


Por ejemplo:

- La ruta /user/list/ se redireccionaría a /user/list.
- La ruta /user/edit/3/ se redireccionaría a /user/edit/3.
- Las rutas /user/list y /user/edit/3 no se redireccionarían.

