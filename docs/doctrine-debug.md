# Debug

```php
$dql = "SELECT u FROM User u";
$query = $entityManager->createQuery($dql);
var_dump($query->getSQL());

$qb = $entityManager->createQueryBuilder();
$qb->select('u')->from('User', 'u');
var_dump($qb->getDQL());
```

