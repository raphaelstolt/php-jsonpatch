<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\Operations;

class AddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @expectedExceptionMessage Mandatory value property not set
     */
    public function missingManadatoryOperationKeyShouldThrowExpectedException()
    {
        $operation = new \stdClass;
        $operation->salue = 17;
        $operation->path = '/a/b/d';

        $addOperation = new Add($operation);
    }
    /**
     * @test
     * @dataProvider addProvider
     */
    public function shouldAddObjectMemberAsExpected($providerData)
    {
        $targetJson = $providerData['given-json'];
        $expectedJson = $providerData['expected-json'];
        $operation = $providerData['add-operation'];

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }
    /**
     * @test
     */
    public function shouldAddNestedObjectMemberAsExpected()
    {
        $targetJson = '{"foo":"bar"}';
        $expectedJson = '{"foo":"bar","child":{"grandchild": {}}}';

        $value = new \stdClass;
        $value->grandchild = new \stdClass;

        $operation = new \stdClass;
        $operation->path = '/child';
        $operation->value = $value;

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }
    /**
     * @test
     */
    public function shouldAddArrayElementAsExpected()
    {
        $targetJson = '{"foo":["bar","baz"]}';
        $expectedJson = '{"foo":["bar","qux","baz"]}';

        $operation = new \stdClass;
        $operation->path = '/foo/1';
        $operation->value = 'qux';

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }
    /**
     * @test
     */
    public function shouldAddArrayElementToTheEndAsExpected()
    {
        $targetJson = '{"foo":"bar","baz":{"boo":["bar","baz"]}}';
        $expectedJson = '{"foo":"bar","baz":{"boo":["bar","baz","qux"]}}';

        $operation = new \stdClass;
        $operation->path = '/baz/boo/-';
        $operation->value = 'qux';

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 28 (https://github.com/raphaelstolt/php-jsonpatch/issues/28)
     */
    public function shouldKeepObjectsAsObjects()
    {
        $targetJson = '{"foo": {"bar": "baz", "boo": {}}}';
        $expectedJson = '{"foo": {"bar": "baz", "boo": {}, "baz": "qux"}}';

        $operation = new \stdClass;
        $operation->path = '/foo/baz';
        $operation->value = 'qux';

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
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

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @ticket 37 (https://github.com/raphaelstolt/php-jsonpatch/issues/37)
     */
    public function shouldCorrectlyUseNumericIndexInObjectHandling()
    {
        $targetJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"qux"} ] }}}';
        $expectedJson = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"otherValue"}, {"bar":"qux"} ] }}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/baz/1';
        $operation->value = new \stdClass();
        $operation->value->bar = 'otherValue';

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
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

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @test
     */
    public function shouldAddAnArrayValueAsExpected()
    {
        $targetJson = '{"foo":["bar"]}';
        $expectedJson = '{"foo":["bar", ["abc","def"]]}';

        $operation = new \stdClass;
        $operation->path = '/foo/-';
        $operation->value = array('abc', 'def');

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }
    /**
     * @test
     */
    public function shouldAddAnArrayValueToSpecificIndexAsExpected()
    {
        $targetJson     = '{"foo":[{"name": "bar"},{"name": "baz"}]}';
        $expectedJson   = '{"foo":[{"name": "bar"},{"name": "new"},{"name": "baz"}]}';

        $operation = new \stdClass;
        $operation->path = '/foo/1';
        $operation->value = array('name' => 'new');

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }
    /**
     * @test
     * @dataProvider tooLargeArrayIndexProvider
     */
    public function shouldNotAddArrayElementWhenUsingATooLargeIndex($providerData)
    {
        $expectedJson = $targetJson = $providerData['given-json'];

        $operation = $providerData['add-operation'];

        $addOperation = new Add($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $addOperation->perform($targetJson)
        );
    }

    /**
     * @return array
     */
    public function addProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":"bar"}',
                'expected-json' => '{"baz":"qux","foo":"bar"}',
                'add-operation' => (object) array('path' => '/baz', 'value' => 'qux', 'xyz' => 123),
            )),
            array(array(
                'given-json' => '{"foo":"bar"}',
                'expected-json' => '{"foo":"bar"}',
                'add-operation' => (object) array('path' => '/baz/bat', 'value' => 'qux'),
            )),
            array(array(
                'given-json' => '{"foo":"bar"}',
                'expected-json' => '{"foo":"qux"}',
                'add-operation' => (object) array('path' => '/foo', 'value' => 'qux'),
            )),
            array(array(
                'given-json' => '{"foo":["bar","baz"]}',
                'expected-json' => '{"foo":["bar","qux","baz"]}',
                'add-operation' => (object) array('path' => '/foo/1', 'value' => 'qux'),
            )),
            array(array(
                'given-json' => '{"a":{"foo":1}}',
                'expected-json' => '{"a":{"foo":1,"boo":100}}',
                'add-operation' => (object) array('path' => '/a/boo', 'value' => 100),
            )),
            array(array(
                'given-json' => '{"q":{"bar":2}}',
                'expected-json' => '{"q":{"bar":2}}',
                'add-operation' => (object) array('path' => '/a/b', 'value' => 100),
            )),
        );
    }
    /**
     * @return array
     */
    public function tooLargeArrayIndexProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":["bar","baz","qux"]}',
                'add-operation' => (object) array('path' => '/foo/4', 'value' => 'non-set-able'),
            )),
            array(array(
                'given-json' => '{"foo":{"moo":["bar","baz","qux"]}}',
                'add-operation' => (object) array('path' => '/foo/moo/7', 'value' => 'non-set-able'),
            )),
        );
    }
}
