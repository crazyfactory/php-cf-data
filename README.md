# CrazyFactory\Data for PHP

A library for structuring data in your project. Offers model creation,
management and validation base classes. Though it's not meant to replace 
a proper ORM it can be used as an ORM-like approach to several scenarios.

| master |
| --- |
| [![Build Status](https://travis-ci.org/crazyfactory/php-cf-data.svg?branch=master)](https://travis-ci.org/crazyfactory/php-cf-data) |



## Features

General
* Relies on throwing exceptions. Most things don't fail gracefully by design.
* Heavily tested to ensure stability.
* Based on Interfaces and abstractions. Most things can be hot-swapped or extended if desired.

CrazyFactory\Core\Models\Base\ModelBase
* Simple data container class
* Validates all changed properties instantly
* Dirty state tracking for logging, query buiding, ...
* Import and export functions for all properties (or just the dirty ones!)
* See 'CrazyFactory\Core\Models\IdModel' for a simple implementation.
* Can be used with custom getters and setters (it's optional though)

CrazyFactory\Core\Collections\Base\CollectionBase 
* implements ICollection
* adds most of the interfaces functions
* adds some helpful protected methods to be used by your subclass
* hosts an ISerializer instance for conversion
* CrazyFactory\Core\Collections\SqlCollection
  * extends CollectionsBase
  * stores models in a database.
  * can be used directly or via extension

CrazyFactory\Core\Serializers\DataToDataSerializer
* Implements ISerializer
* Extends SerializerBase
* Maps on data array to another one.
  * copies all values by default
  * using a map, single properties can be skipped or renamed
  * for complex scenarios pre and post process callbacks can be supplied.

## Usage

- add a custom repository to your composer.json file
  ```
  "repositories": [
   {
     "type": "vcs",
     "url": "https://github.com/crazyfactory/php-cf-data"
   }
  ]
  ```

- run ```composer require crazyfactory/php-cf-data```

## Testing

- run ``composer update``
- run ``composer test`` for PHPUnit tests.
- run ``composer lint`` for code style tests (linux only)