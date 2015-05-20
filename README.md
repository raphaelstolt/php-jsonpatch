# JSON Patch for PHP

[![Build Status](https://secure.travis-ci.org/raphaelstolt/php-jsonpatch.png)](http://travis-ci.org/raphaelstolt/php-jsonpatch)

This is an implementation of [JSON Patch](http://tools.ietf.org/html/rfc6902) written in PHP.

## Dependencies (managed via [Composer](http://packagist.org/about-composer))

[`JSON Pointer for PHP`](https://github.com/raphaelstolt/php-jsonpointer) by Raphael Stolt.

## Installation via Composer

Download the [`composer.phar`](http://getcomposer.org/composer.phar) executable if nonexistent.

Create or modify **composer.json** in the \_\_ROOT_DIRECTORY__ of your project by adding the `php-jsonpatch/php-jsonpatch` dependency.

    {
        "require": {
            "php-jsonpatch/php-jsonpatch": "dev-master"
        },
        "minimum-stability": "dev"
    }

Run Composer: `php composer.phar install` or `php composer.phar update`

## Usage

Now you can use JSON Patch for PHP via the available Composer **autoload file**.

Patch operations are defined in JSON and bundled in an array. Available JSON Patch
[operations](http://tools.ietf.org/html/rfc6902#section-4) are `add`, `remove`, `replace`, `move`, `copy`,
and `test`; if their mandatory properties are not set a `Rs\Json\Patch\InvalidOperationException` will be
thrown.

    <?php
    require_once 'vendor/autoload.php';

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
          {"op":"copy", "path":"/a/e", "from":"/a/c"}, // {"a":{"b":["c","d","e"],"c":["a","b"],"e":["a","b"]}}
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

For some more usage examples of JSON Patch operations have a look at the
integration tests located under `tests/integration/*`.

## Testing

    $ phpunit

## License

JSON Patch for PHP is licensed under the MIT License

Copyright (c) 2013 - 2015 Raphael Stolt

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
