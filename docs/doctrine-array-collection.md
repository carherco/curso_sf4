# Doctrine Collections

Doctrine Collections es una librería que contiene clases para trabajar con arrays de datos.

```php
<?php

use Doctrine\Common\Collections\ArrayCollection;

$collection = new ArrayCollection([1, 2, 3]);

$filteredCollection = $collection->filter(function($count) {
    return $count > 1;
}); // [2, 3]
```

Esta librería proporciona un interfaz llamado **Doctrine\Common\Collections\Collection** que replica la naturaleza de los arrays de PHP.

Las colecciones son esencialmente mapas ordenados que también pueden ser utilizados como listas.

Tiene un iterador interno *getIterator()*, pero también pueden ser iterados por iteradores externos, como *foreach*.

## Collection Methods

Los métodos disponibles en la interfaz Doctrine\Common\Collections\Collection son:

- add

Añade un elemento al final de la colección.

```php
$collection->add('test');
```

- clear

Elimina todos los elementos de la colección.

```php
$collection->clear();
```

- contains

Comprueba si un elemento existe en la colección.

Para una colección FETCH=LAZY, llamar al método contains() provoca la incialización de la colección. Sin embargo, para FETCH=EXTRA_LAZY, este método utiliza SQL para determinar si la entidad ya es parte de la colección.

```php
$collection = new Collection(['test']);

$contains = $collection->contains('test'); // true
```

- containsKey

Comprueba si una colección contiene un elemento con el índice/clave específico.

```php
$collection = new Collection(['test' => true]);

$contains = $collection->containsKey('test'); // true
```

- current

Devuelve el elemento que se encuentra en la posición actual en el iterador.

```php
$collection = new Collection(['first', 'second', 'third']);

$current = $collection->current(); // first
```

- exists

Comprueba la existencia de algún elemento que satisfaga la condición indicada.

```php
$collection = new Collection(['first', 'second', 'third']);

$exists = $collection->exists(function($key, $value) {
    return $value === 'first';
}); // true
```

- filter

Devuelve todos los elementos de la colección que satisfacen la condición indicada. El orden de los elementos es respetado.

```php
$collection = new ArrayCollection([1, 2, 3]);

$filteredCollection = $collection->filter(function($count) {
    return $count > 1;
}); // [2, 3]
```

- first

Sitúa el iterador interno en la primera posición y devuelve el elemento de dicha posición.

```php
$collection = new Collection(['first', 'second', 'third']);

$first = $collection->first(); // first
```

- forAll

Testea si todos los elementos cumplen la condición indicada.

```php
$collection = new ArrayCollection([1, 2, 3]);

$forAll = $collection->forAll(function($key, $value) {
    return $value > 1;
}); // false
```

- get

Devuelve el elemento con el ínidce/clave específico

```php
$collection = new Collection([
    'key' => 'value',
]);

$value = $collection->get('key'); // value
```

- getKeys

Devuelve todos los índices/claves de la colección

```php
$collection = new Collection(['a', 'b', 'c']);

$keys = $collection->getKeys(); // [0, 1, 2]
```

- getValues

Devuelve todos los valores de la colección.

```php
$collection = new Collection([
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3',
]);

$values = $collection->getValues(); // ['value1', 'value2', 'value3']
```

- indexOf

Devuelve el índice/clave de un elemento dado. La comparación es estricta (===).

```php
$collection = new ArrayCollection([1, 2, 3]);

$indexOf = $collection->indexOf(3); // 2
```

- isEmpty

Comprueba si la colección está vacía (no contiene ningún elemento).

```php
$collection = new Collection(['a', 'b', 'c']);

$isEmpty = $collection->isEmpty(); // false
```

- key

Devuelve el índice/clave del elemento que se encuentra en la posición actual en el iterador.

```php
$collection = new ArrayCollection([1, 2, 3]);

$collection->next();

$key = $collection->key(); // 1
```

- last
Sitúa iterador interno en la última posición y devuelve el elemento de dicha posición.

```php
$collection = new ArrayCollection([1, 2, 3]);

$last = $collection->last(); // 3
```

- map

Aplica la función indicada a cada elemento de la colección y devuelve una nueva colección con los elementos devueltos por la función indicada.

```php
$collection = new ArrayCollection([1, 2, 3]);

$mappedCollection = $collection->map(function($value) {
    return $value + 1;
}); // [2, 3, 4]
```

- next

Sitúa el iterador interno en el siguiente elemento y devuelve dicho elemento.

```php
$collection = new ArrayCollection([1, 2, 3]);

$next = $collection->next(); // 2
```

- partition

Parte la colección en dos colecciónes según la condición indicada. Las claves se preservan.

```php
$collection = new ArrayCollection([1, 2, 3]);

$mappedCollections = $collection->partition(function($key, $value) {
    return $value > 1
}); // [[2, 3], [1]]
```

- remove

Elimina el elemento que ocupa la posición indicada.

```php
$collection = new ArrayCollection([1, 2, 3]);

$collection->remove(0); // [2, 3]
```

- removeElement

Elimina de la colección el elemento indicado, si existe.

```php
$collection = new ArrayCollection([1, 2, 3]);

$collection->removeElement(3); // [1, 2]
```

- set

Setea un elemento en la clave/índice indicado.

```php
$collection = new ArrayCollection();

$collection->set('name', 'jwage');
```

- slice($offset, $length)

Extrae una porción de la colección. Si no se indica $length, extrae desde $offset hasta el final de la colección.

```php
$collection = new ArrayCollection([0, 1, 2, 3, 4, 5]);

$slice = $collection->slice(1, 2); // [1, 2]
```

- toArray

Convierte la colección en un array nativo de PHP.

```php
$collection = new ArrayCollection([0, 1, 2, 3, 4, 5]);

$array = $collection->toArray(); // [0, 1, 2, 3, 4, 5]
```

## Selectable Methods

Algunas colecciones de Doctrine, como **Doctrine\Common\Collections\ArrayCollection**, implementan un interfaz llamado **Doctrine\Common\Collections\Selectable** que ofrece el uso de una potente API de expresiones en las que se pueden aplicar condiciones a la colección para obtener un resultado únicamente con los elementos que cumplan las condiciones.

Esta interfaz implementa el método **matching**.

- matching

Selecciona todos los elementos que cumplan la expresión y devuelve una nueva colección con dichos elementos.

```php
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;

$collection = new ArrayCollection([
    [
        'name' => 'jwage',
    ],
    [
        'name' => 'romanb',
    ],
]);

$expr = new Comparison('name', '=', 'jwage');
$criteria = new Criteria();
$criteria->where($expr);

$matched = $collection->matching($criteria); // ['jwage']
```

### Expressions

https://www.doctrine-project.org/projects/doctrine-collections/en/latest/expressions.html

### Expression Builder

https://www.doctrine-project.org/projects/doctrine-collections/en/latest/expression-builder.html#expression-builder