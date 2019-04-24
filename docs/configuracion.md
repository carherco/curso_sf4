# Configuración

En Symfony4 siguen siendo válidos los 3 formatos de ficheros de configuración: YAML, PHP y XML siendo el preferido y el utilizado por defecto el YAML.

La configuración de cada paquete se encuentra en **config/packages**. Por ejemplo, el FameworkBundle se configura en **config/packages/framework.yaml**

Los ficheros están organizados de otra forma, pero siguien el mismo sistema que en Symfony3. 

Cada bundle/package tiene su clave de primer nivel. 

Sigue existiendo además la sección *parameters* para parametrizar valores.

Se declaran en la sección parameters:

```yaml
# config/services.yaml
parameters:
    locale: en

# ...
```

Y se pueden utilizar después en cualquier fichero de configuración con los porcentajes:

```yaml
# config/packages/translation.yaml
framework:
    # any string surrounded by two % is replaced by that parameter value
    default_locale: '%locale%'

    # ...
```

La novedad en Symfony4 son las llamadas **variables de entorno**.

Para conocer las opciones de configuración de un Bundle tenemos dos opciones:

- Acudir a la documentación oficial de dicho bundle
- Ejecutar el comando config:dump-reference

> php bin/console config:dump-reference framework


## Entornos y variables de entorno

### Las variables de entorno y el fichero .env

- En versión 4.0: 

En el directorio raíz hay un fichero .env en el que se definen vabiables dependientes del entorno y/o del servidor: desarrollo, producción, test, etc.

Este fichero NO se sube al control de versiones. Hay un fichero .env.dist con valores dummy, que sirve como referencia al programador para saber qué valores deben aparecer en el fichero .env.

#### Cambios en las nuevas versiones de Symfony

En las nuevas versiones de symfony se han hecho ciertos cambios en los ficheros .env:

A) El fichero .env.dist ya NO existe

B) El fichero .env se incluye ahora en el control de versiones. Por lo tanto, en este fichero NO se debe incluir información sensible como contraseñas, etc.

C) Existe un nuevo fichero **.env.local** que si existe, tiene preferencia sobre el fichero .env. Este ultimo fichero sí se ignora en GIT con .gitignore.

D) Se puede crear un fichero .env.test para testeo y su correspondiente .env.test.local.

El orden de carga del fichero .env es el siguiente:

1º: .env + entorno + .local (.env.dev.local, .env.test.local, etc).

2º: Si no existe el anterior, se busca el fichero .env.local (excepto para el entorno de test que se busca .env.test)

3º: Si no existe tampoco el fichero buscado en el paso 2, se busca el fichero con nombre .env + entorno (.env.dev, .env.test)

4º: Si no existe ninguno de los anteriores, se carga el fichero .env.

NO SE DEBEN SUBIR AL CONTROL DE VERSIONES ni el fichero .env.local ni los ficheros .env.*.local

### Uso de las variables de entorno en los ficheros de configuración

Cada variable de entorno es transformada por symfony en un parámetro con la siguiente correspondencia

Variable de entorno: NOMBRE_DE_LA_VARIABLE
Parámetro correspondiente: env(NOMBRE_DE_LA_VARIABLE)

Por lo que, para utilizar una variable de entorno en un fichero de configuración es muy sencillo:

```yml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(DATABASE_URL)%'
```

Los valores de estos parámetros son considerados strings por defecto. Este comportamiento se puede cambiar con los procesadores de variables de entorno.

### Environment Variable Processors

https://symfony.com/doc/current/configuration/environment_variables.html#environment-variable-processors

https://symfony.com/blog/new-in-symfony-4-3-url-env-var-processor

https://symfony.com/blog/new-in-symfony-4-3-default-and-trim-env-var-processors

## Entornos

http://symfony.com/doc/master/configuration/environments.html

En el raíz de config/packages se define la configuración común para todos los entornos.

En las subcarpetas dev, prod y test, se definen las configuraciones que difieren en cada entorno.

- Para el entorno *dev*: config/packages/dev/
- Para el entorno *prod*: config/packages/prod/
- Para el entorno *test*: config/packages/test/


En el fichero *src/Kernel.php* se puede cambiar la localización de estos ficheros.

### Ejecutar Symfony con un entorno específico

Para ejecutar la aplicación en un entorno concreto, basta con cambiar la variable **APP_ENV** del fichero *.env*.

```
# .env
APP_ENV=test
```

Si inspeccionamos el fichero public/index.php veremos cómo funciona la variable:

```php
// public/index.php

// ...
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $_SERVER['APP_DEBUG'] ?? false);

// ...
```

El segundo parámetro de Kernel es si se ejecuta en modo debug.

La misma técnica se utiliza en el script bin/console:

```php
$input = new ArgvInput();
$env = $input->getParameterOption(['--env', '-e'], $_SERVER['APP_ENV'] ?? 'dev', true);
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== $env)) && !$input->hasParameterOption('--no-debug', true);

// ...
 
$kernel = new Kernel($env, $debug);
$application = new Application($kernel);
$application->run($input);
```

## Creación de un entorno personalizado

Por defecto, Symfony 4 viene con los 3 entornos de sobra conocidos por todos: prod, dev y test. No obstante, al igual que en la versión 3, en la 4 podemos crear nuestro propio entorno personalizado.

### Crear un entorno

Para crear un entorno, basta con crear un directorio en *config/packages* con el nombre del entorno y poner allí la configuración específica del entorno.

Podemos reutilizar archivos de configuración utilizando la clave *imports* ya existenete en symfony 3.

```yml
imports:
    - { resource: .../dev/monolog.yaml }
    - { resource: .../dev/swiftmailer.yaml }
```

Más información sobre la clave *imports* y su utilidad para organizar ficheros de configuración:

https://symfony.com/doc/current/configuration/configuration_organization.html
