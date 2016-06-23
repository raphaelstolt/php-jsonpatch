# JSON Patch for PHP

[![Build Status](https://secure.travis-ci.org/raphaelstolt/php-jsonpatch.png)](http://travis-ci.org/raphaelstolt/php-jsonpatch) [![Version](http://img.shields.io/packagist/v/php-jsonpatch/php-jsonpatch.svg?style=flat)](https://packagist.org/packages/php-jsonpatch/php-jsonpatch) [![PHP Version](http://img.shields.io/badge/php-5.4+-ff69b4.svg)](https://packagist.org/packages/php-jsonpatch/php-jsonpatch)

This is an implementation of [JSON Patch](http://tools.ietf.org/html/rfc6902) written in PHP.

### Installation via Composer

``` bash
$ composer require php-jsonpatch/php-jsonpatch
```

### Usage

Now you can use JSON Patch for PHP via the available Composer **autoload file**.

Patch operations are defined in JSON and bundled in an array. Available JSON Patch
[operations](http://tools.ietf.org/html/rfc6902#section-4) are `add`, `remove`, `replace`, `move`, `copy`,
and `test`; if their mandatory properties are not set a `Rs\Json\Patch\InvalidOperationException` will be
thrown.

You can if necessary disable some operations by setting a whitelist bitmask in the constructor.
For example: `$document = new Document($patchDocument, Add::APPLY | Copy::APPLY | Replace::APPLY | Remove::APPLY);`
This will not allow to use move or test in the document. If used, these operations are just ignored.
The default is to allow all operations.

``` php
<?php require_once 'vendor/autoload.php';

use Rs\Json\Patch;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\InvalidOperationException;

try {
    $targetDocument = '{"a":{"b":["c","d","e"]}}';

    $patchDocument = '[
        {"op":"add", "path":"/a/d", "value":["a","b"]}, // {"a":{"b":["c","d","e"],"d":["a","b"]}}
        {"op":"test", "path":"/a/d/-", "value":"b"}, // previous target document
        {"op":"remove", "path":"/a/d/-"}, // {"a":{"b":["c","d","e"],"d":["a"]}}
        {"op":"test", "path":"/a/d/-", "value":"a"}, // previous target document
        {"op":"add", "path":"/a/d/-", "value":"b"}, // {"a":{"b":["c","d","e"],"d":["a","b"]}}
        {"op":"test", "path":"/a/d", "value":["a","b"]}, // previous target document
        {"op":"move", "path":"/a/c", "from":"/a/d"}, // {"a":{"b":["c","d","e"],"c":["a","b"]}}
        {"op":"test", "path":"/a/c", "value":["a","b"]}, // previous target document
        {"op":"copy", "path":"/a/e", "from":"/a/c"}, //   {"a":{"b":["c","d","e"],"c":["a","b"],"e":["a","b"]}}
        {"op":"test", "path":"/a/e", "value":["a","b"]}, // previous target document
        {"op":"replace", "path":"/a/e", "value":["a"]}, // {"a":{"b":["c","d","e"],"c":["a","b"],"e":["a"]}}
        {"op":"test", "path":"/a/e", "value":["a"]} // previous target document
    ]';

    $patch = new Patch($targetDocument, $patchDocument);
    $patchedDocument = $patch->apply(); // {"a":{"b":["c","d","e"],"c":["a","b"],"e":["a"]}}
} catch (InvalidPatchDocumentJsonException $e) {
    // Will be thrown when using invalid JSON in a patch document
} catch (InvalidTargetDocumentJsonException $e) {
    // Will be thrown when using invalid JSON in a target document
} catch (InvalidOperationException $e) {
    // Will be thrown when using an invalid JSON Pointer operation (i.e. missing property)
}
```
For some more usage examples of JSON Patch operations have a look at the
integration tests located under `tests/integration/*`.

### Running tests

``` bash
$ composer test
```

### License

This library is licensed under the MIT License. Please see [LICENSE](LICENSE.md) for more information.

### Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information.

### Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for more information.
