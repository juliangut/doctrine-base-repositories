[![PHP version](https://img.shields.io/badge/PHP-%3E%3D7.0-8892BF.svg?style=flat-square)](http://php.net)
[![Latest Version](https://img.shields.io/packagist/vpre/juliangut/doctrine-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-repositories)
[![License](https://img.shields.io/github/license/juliangut/doctrine-repositories.svg?style=flat-square)](https://github.com/juliangut/doctrine-repositories/blob/master/LICENSE)

[![Build Status](https://img.shields.io/travis/juliangut/doctrine-repositories.svg?style=flat-square)](https://travis-ci.org/juliangut/doctrine-repositories)
[![Style Check](https://styleci.io/repos/69763902/shield)](https://styleci.io/repos/69763902)
[![Code Quality](https://img.shields.io/scrutinizer/g/juliangut/doctrine-repositories.svg?style=flat-square)](https://scrutinizer-ci.com/g/juliangut/doctrine-repositories)
[![Code Coverage](https://img.shields.io/coveralls/juliangut/doctrine-repositories.svg?style=flat-square)](https://coveralls.io/github/juliangut/doctrine-repositories)

[![Total Downloads](https://img.shields.io/packagist/dt/juliangut/doctrine-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-repositories)
[![Monthly Downloads](https://img.shields.io/packagist/dm/juliangut/doctrine-repositories.svg?style=flat-square)](https://packagist.org/packages/juliangut/doctrine-repositories)

# doctrine-repositories

Doctrine2 utility repositories

## Installation

### Composer

```
composer require juliangut/doctrine-repositories
```

If using MongoDB on PHP >= 7.0

```
composer require alcaeus/mongo-php-adapter --ignore-platform-reqs
```

If using CouchDB

```
composer require juliangut/doctrine-manager-builder
```

## Usage

### Use repositoryClass on mapped classes

#### ORM

```php
/**
 * Comment entity.
 *
 * @ORM\Entity(repositoryClass="\Jgut\Doctrine\Repository\RelationalRepository")
 */
class Comment
{
}
```

#### MongoDB ODM

```php
/**
 * Comment MongoDB document.
 *
 * @ODM\Entity(repositoryClass="\Jgut\Doctrine\Repository\MongoDBRepository")
 */
class Comment
{
}
```

#### CouchDB ODM

```php
/**
 * Comment CouchDB document.
 *
 * @ODM\Entity(repositoryClass="\Jgut\Doctrine\Repository\CouchDBRepository")
 */
class Comment
{
}
```

### Register factory on managers

When creating object managers you can set a repository factory to create default repositories such as follows

> For an easier way of registering repository factories and managers generation in general have a look at [juliangut/doctrine-manager-builder](https://github.com/juliangut/doctrine-manager-builder)

#### ORM

```php
use Jgut\Doctrine\Repository\Factory\RelationalRepositoryFactory;

$config = new \Doctrine\ORM\Configuration;
$config->setRepositoryFactory(new RelationalRepositoryFactory);

$entityManager = \Doctrine\ORM\EntityManager::create([], $config);
```

#### MongoDB ODM

```php
use Jgut\Doctrine\Repository\Factory\MongoDBRepositoryFactory;

$config = new \Doctrine\ODM\MongoDB\Configuration;
$config->setRepositoryFactory(new MongoDBRepositoryFactory);

$documentManager = \Doctrine\ODM\MongoDB\DocumentManager::create(new \Doctrine\MongoDB\Connection(...), $config);
```

#### CouchDB ODM

```php
use Jgut\Doctrine\ManagerBuilder\CouchDB\DocumentManager;
use Jgut\Doctrine\Repository\Factory\CouchDBRepositoryFactory;

$documentManager = DocumentManager::create([], new \Doctrine\ODM\CouchDB\Configuration);
$documentManager->setRepositoryFactory(new CouchDBRepositoryFactory);
```

Mind that CouchDB configuration does not support setting repository factory. [juliangut/doctrine-manager-builder](https://github.com/juliangut/doctrine-manager-builder) is a mandatory requirement if you want to use CouchDBRepositoryFactory.

### New methods

This are the new methods that `juliangut/doctrine-repositories` repositories brings to the table. 

#### Creating

##### getNew

Creates a new empty object directly from repository.

```php
$repository = $manager->getRepository(ObjectClass::class);

$newObject = $repository->getNew();
```

##### findOneByOrGetNew

Returns an object based on criteria or a new empty object if could not be found   

```php
$repository = $manager->getRepository(ObjectClass::class);

$existingOrNewObject = $repository->findOneByorGetNew(['slug' => 'my_slug']);
```

#### Adding

##### add

Will persist the entity into the manager.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->add(new ObjectClass());
```

#### Removing

##### remove

In the same fashion as `add` this will remove the entity.

```php
$repository = $manager->getRepository(ObjectClass::class);

$managedObject = $repository->findById(1);

$repository->remove($managedObject);
```

##### removeAll

FindAll and then removes them all.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->removeAll();
```

##### removeBy and removeOneBy

As their counter parts findBy and findOneBy but removing the objects instead of returning them.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->removeBy(['active' => false]);
$repository->removeByActive(false);
$repository->removeOneBy(['id' => 1]);
$repository->removeOneById(1);
```

#### Counting

##### countAll and countBy

Perform object count in the most efficient way possible, except for CouchDB ;-(

```php
$repository = $manager->getRepository(ObjectClass::class);

$totalObjects = $repository->countAll();
$activeObjects = $repository->countBy(['active' => true]);
```

_CountBy accepts an instance of \Doctrine\ORM\QueryBuilder and \Doctrine\ODM\MongoDB\Query\Builder to allow more criteria control._

#### Paginating

Returns the same results that `findBy` would return but within a `\Zend\Paginator\Paginator` object with pagination information. 

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

_Accepts an instance of \Doctrine\ORM\QueryBuilder or \Doctrine\ODM\MongoDB\Query\Builder to allow more criteria control_

_Mind that pagination on CouchDB is **very** inefficient as it fetches all results and then cycles through the returning array._

### Events managing

It is common to have event subscribers on manager's event manager. This is usually due to the use of Doctrine extensions that add extra behaviour in certain points of the lifecycle. [gedmo/doctrine-extensions](https://github.com/Atlantic18/DoctrineExtensions) is an example of such behaviours.

#### Disabling event subscribers

You might want to temporarily disable an event subscriber.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventSubscriber(\Gedmo\Timestampable\TimestampableListener::class);
$repository->save(new EntityClass());
$repository->restoreEventSubscribers();
```

#### Disabling an event listeners

You might want to disable all listeners on a certain event.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventListeners('onFlush');
$repository->save(new EntityClass());
$repository->restoreEventListeners('onFlush');
// $repository->restoreAllEventListener();
```

#### Disabling single event listeners

You might want to disable certain listeners and not all listeners registered for an event.

```php
$repository = $manager->getRepository(ObjectClass::class);

$repository->disableEventListener('onFlush', \Gedmo\Loggable/LoggableListener::class);
$repository->save(new EntityClass());
$repository->restoreEventListener('onFlush');
// $repository->restoreAllEventListener();
```

## Contributing

Found a bug or have a feature request? [Please open a new issue](https://github.com/juliangut/doctrine-repositories/issues). Have a look at existing issues before.

See file [CONTRIBUTING.md](https://github.com/juliangut/doctrine-repositories/blob/master/CONTRIBUTING.md)

## License

See file [LICENSE](https://github.com/juliangut/doctrine-repositories/blob/master/LICENSE) included with the source code for a copy of the license terms.
