# Extract Transform Load - Abstraction

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Latest Stable Version](https://poser.pugx.org/flow-php/etl/v)](https://packagist.org/packages/flow-php/etl)
[![Latest Unstable Version](https://poser.pugx.org/flow-php/etl/v/unstable)](https://packagist.org/packages/flow-php/etl)
[![License](https://poser.pugx.org/flow-php/etl/license)](https://packagist.org/packages/flow-php/etl)
![Tests](https://github.com/flow-php/etl/workflows/Tests/badge.svg?branch=1.x)

## Description

Flow PHP ETL is a simple ETL (Extract Transform Load) abstraction designed to implement Filters & Pipes architecture.

## Typical Use Cases

* Sync data from external systems (API)
* File processing
* Pushing data to external systems
* Data migrations

Using this library makes sense when we need to move data from one place to another, doing some transformations in between.

For example, let's say we must synchronize data from external API periodically, transform them into our internal
data structure, filter out things that didn't change, and load in bulk into the database.

This is a perfect scenario for ETL.

## Features

* Low memory consumption even when processing thousands of records
* Type safe Rows/Row/Entry abstractions
* Filtering
* Built in Rows objects comparison
* Rich collection of Row Entries 

## Row Entries

* [ArrayEntry](src/Flow/ETL/Row/Entry/ArrayEntry.php)
* [BooleanEntry](src/Flow/ETL/Row/Entry/BooleanEntry.php)
* [CollectionEntry](src/Flow/ETL/Row/Entry/CollectionEntry.php)
* [DateTimeEntry](src/Flow/ETL/Row/Entry/DateTimeEntry.php)
* [FloatEntry](src/Flow/ETL/Row/Entry/FloatEntry.php)
* [IntegerEntry](src/Flow/ETL/Row/Entry/IntegerEntry.php)
* [NullEntry](src/Flow/ETL/Row/Entry/NullEntry.php)
* [ObjectEntryEntry](src/Flow/ETL/Row/Entry/ObjectEntry.php)
* [StringEntry](src/Flow/ETL/Row/Entry/StringEntry.php)
* [StructureEntry](src/Flow/ETL/Row/Entry/StructureEntry.php)

## Extensions  

Extension provides generic, not really related to any specific data source/storage transformers/loaders. 

<table style="text-align:center">
<thead>
  <tr>
    <th>Name</th>
    <th>Transformer</th>
    <th>Loader (write)</th>
  </tr>
</thead>
<tbody>
  <tr>
      <td><a href="https://github.com/flow-php/etl-transformer">Transformers</a></td>
      <td>✅</td>
      <td>🚫</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-loader">Loaders</a></td>
      <td>🚫</td>
      <td>✅</td>
  </tr>
</tbody>
</table>

## Adapters

Adapter connects ETL with existing data sources/storages and including some times custom 
data entries. 

<table style="text-align:center">
<thead>
  <tr>
    <th>Name</th>
    <th>Extractor (read)</th>
    <th>Loader (write)</th>
  </tr>
</thead>
<tbody>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-memory">Memory</a></td>
      <td>✅</td>
      <td>✅</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-doctrine">Doctrine - DB</a></td>
      <td>✅</td>
      <td>✅</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-elasticsearch">Elasticsearch</a></td>
      <td>N/A</td>
      <td>✅</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-csv">CSV</a></td>
      <td>✅</td>
      <td>✅</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-json">JSON</a></td>
      <td>✅</td>
      <td>N/A</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-xml">XML</a></td>
      <td>✅</td>
      <td>N/A</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-http">HTTP</a></td>
      <td>✅</td>
      <td>N/A</td>
  </tr>
  <tr>
      <td><a href="#">Excel</a></td>
      <td>N/A</td>
      <td>N/A</td>
  </tr>
  <tr>
      <td><a href="https://github.com/flow-php/etl-adapter-logger">Logger</a></td>
      <td>🚫</td>
      <td>✅</td>
  </tr>
</tbody>
</table>

* ✅ - at least one implementation is available 
* 🚫 - implementation not possible
* `N/A` - not implementation available yet 

**❗ If adapter that you are looking for is not available yet, and you are willing to work on one, feel free to create one as a standalone repository.**
**Well designed and documented adapters can be pulled into `flow-php` organization that will give them maintenance and security support from the organization.** 

## Installation

```bash
composer require flow-php/etl:1.x@dev
```

## Usage

```php
<?php

use Flow\ETL\ETL;
use Flow\ETL\Extractor;
use Flow\ETL\Loader;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

require_once __DIR__ . '/../vendor/autoload.php';

$extractor = new class implements Extractor {
    public function extract(): Generator
    {
        yield new Rows(
            Row::create(
                new Row\Entry\ArrayEntry('user', ['id' => 1, 'name' => 'Norbret', 'roles' => ['DEVELOPER', 'ADMIN']])
            )
        );
    }
};

$transformer = new class implements Transformer {
    public function transform(Rows $rows): Rows
    {
        return $rows->map(function (Row $row): Row {
            $dataArray = $row->get('user')->value();

            return Row::create(
                new Row\Entry\IntegerEntry('id', $dataArray['id']),
                new Row\Entry\StringEntry('name', $dataArray['name']),
                new Row\Entry\ArrayEntry('roles', $dataArray['roles'])
            );
        });
    }
};

$loader = new class implements Loader {
    public function load(Rows $rows): void
    {
        var_dump($rows->toArray());
    }
};

ETL::extract($extractor)
    ->transform($transformer)
    ->load($loader);
```

## Error Handling 

In case of any exception in transform/load steps, ETL process will break, in order
to change that behavior please set custom [ErrorHandler](src/Flow/ETL/ErrorHandler.php). 

Error Handler defines 3 behavior using 2 methods. 

* `ErrorHandler::throw(\Throwable $error, Rows $rows) : bool`
* `ErrorHandler::skipRows(\Throwable $error, Rows $rows) : bool`

If `throw` returns true, ETL will simply throw an error.
If `skipRows' returns true, ETL will stop processing given rows, and it will try to move to the next batch.
If both methods returns false, ETL will continue processing Rows using next transformers/loaders.

There are 3 build in ErrorHandlers (look for more in adapters):

* [IgnoreError](src/Flow/ETL/ErrorHandler/IgnoreError.php)
* [SkipRows](src/Flow/ETL/ErrorHandler/SkipRows.php)
* [ThrowError](src/Flow/ETL/ErrorHandler/ThrowError.php)

Error Handling can be set directly at ETL:

```php

ETL::extract($extractor)
    ->onError(new IgnoreError())
    ->transform($transformer)
    ->load($loader);
```

## Collect/Parallelize 

Flow PHP ETL is designed to keep memory consumption constant. This can be achieved by processing
only one chunk of data at time. 

It's `Extrator` responsibility to define how big those chunks are, for example when processing CSV file with 10k 
lines, extractor might want to read only 1k lines at once. 

Those 1k lines will be represented as an instance of `Rows`. This means that through ETL pipeline we are
going to push 10 rows, 1k row each. 

Main purpose of methods `ETL::collect()` and `ETL::parallelize()` is to adjust number of rows in the middle of processing.

This means that Extractor can still extract 1k rows at once, but before using loader we can use `ETL::collect` which
will wait for all rows to get extracted, then it will merge them and pass total 10k rows into `Loader`. 

Parallelize method is exactly opposite, it will not wait for all Rows in order to collect them, instead it will
take any incoming Rows instance and split it into smaller chunks according to `ETL::parallelize(int $chunks)` method `chunks` argument.

## Performance 

The most important thing about performance to remember is that creating custom Loaders/Transformers might have negative impact to 
processing performance.

#### ETL::collect() 

Using collect on a large number of rows might end up without of memory exception, but it can also significantly increase
loading time into datasink. It might be cheaper to do one big insert than multiple smaller inserts.

#### Bulk Transformations 

In order to let Flow ETL decide when to merge transformers in order to reduce number of iterations 
pass multiple transformers into `ETL::transform(Transformer ...$transformers) : self` method.

Example:

```php
<?php 

ETL::extract(...)
    ->transform(
        new Transformer(),
        new Transformer(),
        new Transformer(),
        new Transformer(),
    )
```

This way ETL will try to execute all transformations during single iteration through `Rows`.

Normally, each Transformer iterates through `Rows`, having 5 transformers, extracting 10 `Rows`, each with 1k `Row` 
ETL must perform `10 * 1k * 5 = 50k` iterations in total. 

Bulk transformation will reduce that number 5 times, merging all transforming operation into single iteration through 
all rows, so `10 * 1k = 10k`. 

## Development

In order to install dependencies please, launch following commands:

```bash
composer install
```

## Run Tests

In order to execute full test suite, please launch following command:

```bash
composer build
```

It's recommended to use [pcov](https://pecl.php.net/package/pcov) for code coverage however you can also use
xdebug by setting `XDEBUG_MODE=coverage` env variable.
