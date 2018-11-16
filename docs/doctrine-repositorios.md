Los repositorios
================

La clase Repository nos ofrece automáticamente varios métodos muy útiles para obtener registros de la base de datos:

- find($id)
- findOneByXXX()
- findByXXX
- findAll
- findOneBy
- findBy

```php
$repository = $this->getDoctrine()->getRepository(Grado::class);

// Obtener un grado buscando por su primary key (normalmente "id")
$grado = $repository->find($gradoId);

// Métodos dinámicos para obtener un grado buscando por el valor de una columna
$grado = $repository->findOneById($gradoId);
$grado = $repository->findOneByNombre('Ingeniería de montes');

// Métodos dinámicos para obtener un array de objetos grado buscando por el valor de una columna
$grados = $repository->findByNombre('Ingeniería de montes');

// Obtener todos los grados
$grados = $repository->findAll();

// filtrar por varios campos: una entidad
$grado = $repository->findOneBy(
    array('nombre' => 'montes', 'credects' => 6)
);

// filtrar por varios campos: array de entidades
$grados = $repository->findBy(
    array('nombre' => 'matematicas'),
    array('credects' => 'ASC')
);

// filtrar por asociación (owning side)
$number = $em->find('Entity\Phonenumber', 1234);
$user = $em->getRepository('Entity\User')->findOneBy(array('phone' => $number->getId()));

// con order by, limit y offset
$tenUsers = $em->getRepository('Entity\User')->findBy(array('age' => 20), array('name' => 'ASC'), 10, 0);

// SELECT * FROM users WHERE age IN (20, 30, 40)
$users = $em->getRepository('Entity\User')->findBy(array('age' => array(20, 30, 40)));
```

También nos ofrece un método para contar elementos:

- count()

```php
$grado = $repository->count(
    array('nombre' => 'montes', 'credects' => 6)
);
```

Estos métodos realizan búsquedas exactas (con el operador '='). Si queremos realizar búsquedas con el operador LIKE, tenemos que recurrir al lenguaje DQL o al objeto QueryBuilder que veremos más adelante.

API: https://www.doctrine-project.org/api/orm/latest/Doctrine/ORM/EntityRepository.html