# Ingeniería inversa con doctrine

El primer paso para construir las clases de entidad a partir de una base de datos existente, es pedir a doctrine que inspeccione la base de datos y genere los correspondiente archivos de metadatos. Los archivos de metadatos describen las entidades que se deben generar a partir de los campos de las tablas:

> php bin/console doctrine:mapping:import App\\Entity annotation --path=src/Entity

Las entidades generadas son clases con anotaciones y metadata, pero no tienen los métodos getters y setters.

Para generar dichos métodos, tenemos el siguiente comando:

> bin/console make:entity --regenerate App
