[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-base-repositories)
[![License](https://img.shields.io/github/license/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://github.com/juliangut/doctrine-base-repositories/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://travis-ci.org/juliangut/doctrine-base-repositories)
[![Style Check](https://styleci.io/repos/69763902/shield)](https://styleci.io/repos/69763902)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/doctrine-base-repositories)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://coveralls.io/github/juliangut/doctrine-base-repositories)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-base-repositories)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/doctrine-base-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-base-repositories)

# doctrine-base-repositories

Doctrine2 utility repositories. Use as a base for custom repositories

## Installation

### Composer

```
composer require juliangut/doctrine-base-repositories
```

## Use

Create your custom repository implementing Repository interface

```php
use Doctrine\ORM\EntityRepository;
use Jgut\Doctrine\Repository\EventsTrait;
use Jgut\Doctrine\Repository\FiltersTrait;
use Jgut\Doctrine\Repository\PaginatorTrait;
use Jgut\Doctrine\Repository\Repository;
use Jgut\Doctrine\Repository\RepositoryTrait;

class customRepository extends EntityRepository implements Repository
{
    use RepositoryTrait;
    use EventsTrait;
    use FiltersTrait;
    use PaginatorTrait;

    protected function getFilterCollection()
    {
        // Custom implementation
    }

    public function countBy($criteria)
    {
        // Custom implementation
    }

    public function findPaginatedBy($criteria, array $orderBy = [], $itemsPerPage = 10)
    {
        // Custom implementation
    }

    protected function getManager()
    {
        // Custom implementation
    }

    // Custom methods
}
```

### Implementations

* ORM (Relational databases) with [doctrine-orm-repositories](https://github.com/juliangut/doctrine-orm-repositories)
* MongoDB with [doctrine-mongodb-odm-repositories](https://github.com/juliangut/doctrine-mongodb-odm-repositories)
* CouchDB with [doctrine-couchdb-odm-repositories](https://github.com/juliangut/doctrine-couchdb-odm-repositories)

## New methods

These are the new methods that `juliangut/doctrine-base-repositories` brings to the table thanks to `RepositoryTrait` 

### Creating

#### getNew

Creates a new empty object directly from repository.

```php
$repository = $manager->getRepository(ObjectClass::class);

$newObject = $repository->getNew();
```

#### findOneByOrGetNew

Returns an object based on criteria or a new empty object if could not be found   

```php
$repository = $manager->getRepository(ObjectClass::class);

$existingOrNewObject = $repository->findOneByorGetNew(['slug' => 'my_slug']);
```

### Adding

#### add

Will persist the entity into the manager.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->add(new ObjectClass());
```

### Removing

#### remove

In the same fashion as `add` this will remove the entity.

```php
$repository = $manager->getRepository(ObjectClass::class);

$managedObject = $repository->findById(1);

$repository->remove($managedObject);
```

#### removeAll

FindAll and then removes them all.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->removeAll();
```

#### removeBy and removeOneBy

As their counter parts findBy and findOneBy but removing the objects instead of returning them.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->removeBy(['active' => false]);
$repository->removeByActive(false);
$repository->removeOneBy(['id' => 1]);
$repository->removeOneById(1);
```

### Counting

#### countAll and countBy

Perform object count

```php
$repository = $manager->getRepository(ObjectClass::class);

$totalObjects = $repository->countAll();
$activeObjects = $repository->countBy(['active' => true]);
```

_countBy needs implementation on custom repository_

## Events managing

It is common to have event subscribers on manager's event manager. This is usually due to the use of Doctrine extensions that add extra behaviour in certain points of the lifecycle. [gedmo/doctrine-extensions](https://github.com/Atlantic18/DoctrineExtensions) is an example of such behaviours.

Events managing is provided by `EventsTrait`

### Disabling event subscribers

You might want to temporarily disable an event subscriber.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventSubscriber(\Gedmo\Timestampable\TimestampableListener::class);
$repository->save(new EntityClass());
$repository->restoreEventSubscribers();
```

### Disabling an event listeners

You might want to disable all listeners on a certain event.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventListeners('onFlush');
$repository->save(new EntityClass());
$repository->restoreEventListeners('onFlush');
// $repository->restoreAllEventListener();
```

### Disabling single event listeners

You might want to disable certain listeners and not all listeners registered for an event.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventListener('onFlush', \Gedmo\Loggable/LoggableListener::class);
$repository->save(new EntityClass());
$repository->restoreEventListener('onFlush');
// $repository->restoreAllEventListener();
```

## Filters managing

Filters managing is provided by `FiltersTrait`

### Disabling filters

You might want to temporarily disable all filters.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableFilters();
$repository->save(new EntityClass());
$repository->restoreFilters();
```

### Disabling a single filter

You might want to disable a single filter.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableFilter('locale');
$repository->save(new EntityClass());
$repository->restoreFilter('locale');
// $repository->restoreFilters();
```

_requires the implementation of getFilterCollection method on custom repository_

## Paginating

Returns the same results that `findBy` would return but within a `\Zend\Paginator\Paginator` object with pagination information. Provided by `PaginatorTrait`

```php
$repository = $manager->getRepository(ObjectClass::class);

$paginator = $repository->findPaginatedBy(['active' => true], ['date' => 'ASC'], 10);
$paginator = $repository->findPaginatedByActive(true, ['date' => 'ASC'], 10);

// Assuming there are 80 "active"
$paginator->getTotalItemCount(); // 80
$paginator->getCurrentItemCount(); // 10
$paginator->getCurrentPageNumber(); // 1
...
```

_needs implementation on custom repository_

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/doctrine-base-repositories/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/doctrine-base-repositories/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/doctrine-base-repositories/blob/master/LICENSE) included with the source code for a copy of the license terms.
