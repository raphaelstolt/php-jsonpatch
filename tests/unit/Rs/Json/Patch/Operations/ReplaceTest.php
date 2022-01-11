<?php
namespace Rs\Json\Patch\Operations;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operations\Replace;

class ReplaceTest extends TestCase
{
    /**
     * @test
     */
    public function missingManadatoryOperationKeyShouldThrowExpectedException()
    {
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('Mandatory value property not set');

        $operation = new \stdClass;
        $operation->path = '/a/b/c';
        $operation->volume = 42;

        $moveOperation = new Replace($operation);
    }

    /**
     * @test
     * @dataProvider replaceProvider
     */
    public function shouldReplaceAsExpected($providerData)
    {
        $targetJson = $providerData['given-json'];
        $expectedJson = $providerData['expected-json'];
        $operation = $providerData['replace-operation'];

        $addOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @test
     */
    public function shouldNotReplaceOnNonexistentPath()
    {
        $targetJson = $expectedJson = '{"baz":"qux","foo":"bar"}';

        $operation = new \stdClass;
        $operation->path = '/buz';
        $operation->value = 'boo';

        $addOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 30 (https://github.com/raphaelstolt/php-jsonpatch/issues/30)
     */
    public function shouldKeepObjectsAsObjects()
    {
        $targetJson = '{"foo": {"bar": "baz", "boo": {}}}';
        $expectedJson = '{"foo": {"bar": "bing", "boo": {}}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar';
        $operation->value = 'bing';

        $replaceOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $replaceOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 33 (https://github.com/raphaelstolt/php-jsonpatch/issues/33)
     */
    public function shouldPreserveEmptyObjectSameLevel()
    {
        $targetJson = '{"foo": {"bar": {"baz": {"boo": {}, "qux": "value"}}}}';
        $expectedJson = '{"foo": {"bar": {"baz": {"boo": {}, "qux": "otherValue"}}}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/baz/qux';
        $operation->value = 'otherValue';

        $replaceOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $replaceOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 37 (https://github.com/raphaelstolt/php-jsonpatch/issues/37)
     */
    public function shouldCorrectlyUseNumericIndexInObjectHandling()
    {
        $targetJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"qux"} ] }}}';
        $expectedJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"otherValue"} ] }}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/baz/1';
        $operation->value = '{"bar":"otherValue"}';

        $replaceOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $replaceOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 37 (https://github.com/raphaelstolt/php-jsonpatch/issues/37)
     */
    public function shouldCorrectlyUseNumericIndexInObjectHandlingWithAddedSubProp()
    {
        $targetJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"qux"} ] }}}';
        $expectedJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"otherValue"} ] }}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/baz/1/bar';
        $operation->value = 'otherValue';

        $replaceOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $replaceOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 5 (https://github.com/raphaelstolt/php-jsonpatch/issues/5)
     */
    public function shouldReplaceWhenPathValueIsNull()
    {
        $targetJson = '{"foo":"bar","baz":null}';

        $operation = new \stdClass;
        $operation->path = '/baz';
        $operation->value = 'bing';

        $expectedJson = '{"foo":"bar","baz":"bing"}';

        $addOperation = new Replace($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @return array
     */
    public function replaceProvider()
    {
        return array(
            array(array(
                'given-json' => '{"a":{"b":{"c":100}}}',
                'expected-json' => '{"a":{"b":{"c":42}}}',
                'replace-operation' => (object) array('path' => '/a/b/c', 'value' => 42),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":"bar"}',
                'expected-json' => '{"baz":"boo","foo":"bar"}',
                'replace-operation' => (object) array('path' => '/baz', 'value' => 'boo'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":true}',
                'expected-json' => '{"baz":"qux","foo":false}',
                'replace-operation' => (object) array('path' => '/foo', 'value' => false),
            )),
            array(array(
                'given-json' => '{"a":{"b":{"null":100}}}',
                'expected-json' => '{"a":{"b":{"null":0}}}',
                'replace-operation' => (object) array('path' => '/a/b/null', 'value' => 0),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":"bar"}',
                'expected-json' => '{"baz":"qux","foo":"boo"}',
                'replace-operation' => (object) array('path' => '/foo', 'value' => 'boo'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b", "c"]}',
                'expected-json' => '{"baz":"qux","foo":["a", "b", "cc"]}',
                'replace-operation' => (object) array('path' => '/foo/2', 'value' => 'cc'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b", "c", "d"]}',
                'expected-json' => '{"baz":"qux","foo":["a", "b", "c", "dd"]}',
                'replace-operation' => (object) array('path' => '/foo/-', 'value' => 'dd'),
                )
            ),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b", "c", "d"]}',
                'expected-json' => '{"baz":"qux","foo":[1, 2, 3, 4]}',
                'replace-operation' => (object) array('path' => '/foo', 'value' => '[1, 2, 3, 4]'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b", "c", "d"]}',
                'expected-json' => '{"baz":"qux","foo":["a", "b", "c", ["dd", "ee"]]}',
                'replace-operation' => (object) array('path' => '/foo/-', 'value' => '["dd", "ee"]'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b"]}',
                'expected-json' => '{"baz":"qux","foo":["a", ["bb", "cc"]]}',
                'replace-operation' => (object) array('path' => '/foo/1', 'value' => '["bb", "cc"]'),
            )),
            array(array(
                'given-json' => '{"baz":"qux","foo":["a", "b"]}',
                'expected-json' => '{"baz":"qux","foo":[["a1", "a2"], "b"]}',
                'replace-operation' => (object) array('path' => '/foo/0', 'value' => '["a1", "a2"]'),
            )),
        );
    }
}
