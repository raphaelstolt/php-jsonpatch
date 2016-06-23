### v2.0.1
  * Several refactorings and improvements contributed by [@greydnls](https://github.com/greydnls)

### v2.0.0
  * Dropped PHP 5.3 support to avoid escaped unicode [#21](https://github.com/raphaelstolt/php-jsonpatch/issues/21)

### v1.3.1
  * Fixed issue with deep JSON pointers, see pull request [#20](https://github.com/raphaelstolt/php-jsonpatch/pull/20)

### v1.3.0
  * Introduction of a base exception class

### v1.2.6
  * Keep type of integerish strings in `test` operation

### v1.2.5
  * Ensure library works by utilising mbstring polyfill

### v1.2.4
  * Cleaned docblocks
  * Removed unused variables, imports
  * Keep the type of the JSON the same before and after patching

### v1.2.3
  * Fixed issue [#10](https://github.com/raphaelstolt/php-jsonpatch/issues/10)

### v1.2.2
  * Fixed issue [#8](https://github.com/raphaelstolt/php-jsonpatch/issues/8)

### v1.2.1
  * Fixed issue [#6](https://github.com/raphaelstolt/php-jsonpatch/issues/6)

### v1.2.0
  * Official release

### v1.2.0-RC1
  * More fine-grained exceptions for invalid Json documents, fixes issue [#3](https://github.com/raphaelstolt/php-jsonpatch/issues/3)

### v1.1.0-RC1
  * Uses [JSON Pointer](https://github.com/raphaelstolt/php-jsonpointer) the handling of special URI Fragment identifier #

### v1.0.0-RC1
  * Initial release based on JSON Patch [RFC 6902](http://tools.ietf.org/html/rfc6902)

### v1.0.0-RC2
  * Fixed issue [#2](https://github.com/raphaelstolt/php-jsonpatch/issues/2)
