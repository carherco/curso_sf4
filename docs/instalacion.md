# Instalación

## Creación del proyecto

- Creación de un esqueleto para proyecto web tradicional:

> composer create-project symfony/website-skeleton my-project


- Creación de un esqueleto para proyecto sin visualización web (APIs, commandos...)

> composer create-project symfony/skeleton my-project


# Arrancar el servidor


> php bin/console server:run


Este comando hace uso interno del php -S por lo que tiene modificadores como 

> php bin/console server:start 0.0.0.0:8000


## Nueva estructura de directorios

### Directorios

- assets
- bin
- config
- public
- src
- templates
- tests
- translations
- var
- vendor

```
project/
├── assets/
├── bin/
│   └── console
├── config/
│   ├── bundles.php
│   ├── packages/
│   ├── routes.yaml
│   └── services.yaml
├── public/
│   └── index.php
├── src/
│   ├── ...
│   └── Kernel.php
├── templates/
├── tests/
├── translations/
├── var/
└── vendor/
```

La diferencia sustancial entre la estructura de un proyecto symfony3 y un proyecto symfony4 es **que desaparece el bundle en nuestro proyecto**.

Seguimos teniendo la opción de incorporar bundles de terceros a nuestro proyecto, pero nuestro proyecto en sí ya no será un bundle.

Esto ha permitido a los desarrolladores de symfony poder sacar muchos elemenos a directorios de primer nivel:

- templates: para las plantillas de twig
- tests: para los tests
- translations: para los ficheros de diccionario
- config: para los ficheros de configuración 
- assets: para los assets

El directorio *web* se ha renombrado a *public*.

### Archivos

- .env
- .env.dist
- . gitignore
- composer.json
- composer.lock
- package.json
- phpunit.xml.dist
- symfony.lock
- webpack.config.js


## Actualizar de Symfony 3 a Symfony 4

- [Actualización v3 a v4](./actualizacion.md)

- https://symfony.com/doc/current/setup/upgrade_major.html