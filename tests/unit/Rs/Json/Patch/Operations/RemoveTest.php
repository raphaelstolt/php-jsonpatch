<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\Operations;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function assertMandatoriesShouldThrowNoException()
    {
        $operation = new \stdClass;
        $operation->path = '/a/b';

        $removeOperation = new Remove($operation);

        $this->assertEquals('remove', $removeOperation->getName());
    }
    /**
     * @test
     */
    public function shouldNotRemoveElementWhenPathDoesNotExist()
    {
        $expectedJson = $targetJson = '{"foo":["bar","baz","qux"]}';

        $operation = new \stdClass;
        $operation->path = '/foo/4';

        $removeOperation = new Remove($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $removeOperation->perform($targetJson)
        );
    }
    /**
     * @test
     * @ticket 35 (https://github.com/raphaelstolt/php-jsonpatch/issues/35)
     */
    public function shouldPreserveEmptyObject()
    {
        $targetJson = '{"foo":{"bar":{"baz": {}, "qux": "val"}}, "bar": {}}';
        $expectedJson = '{"foo":{"bar":{"baz": {}}}, "bar": {}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/qux';

        $removeOperation = new Remove($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $removeOperation->perform($targetJson)
        );
    }
    /**
     * @test
     * @ticket 35 (https://github.com/raphaelstolt/php-jsonpatch/issues/35)
     */
    public function shouldPreserveEmptyObjectNumericObjectProperties()
    {
        $targetJson = '{"foo":{"bar":{"baz": {}, "3": "val"}}, "bar": {}}';
        $expectedJson = '{"foo":{"bar":{"baz": {}}}, "bar": {}}';

        $operation = new \stdClass;
        $operation->path = '/foo/bar/3';

        $removeOperation = new Remove($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $removeOperation->perform($targetJson)
        );
    }
    /**
     * @test
     * @dataProvider removeProvider
     */
    public function shouldRemoveAsExpected($providerData)
    {
        $targetJson = $providerData['given-json'];
        $expectedJson = $providerData['expected-json'];
        $operation = $providerData['remove-operation'];

        $removeOperation = new Remove($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $removeOperation->perform($targetJson)
        );
    }

    /**
     * @return array
     */
    public function removeProvider()
    {
        return array(
            array(array(
                'given-json' => '{"baz":"qux", "foo":"bar"}',
                'expected-json' => '{"foo":"bar"}',
                'remove-operation' => (object) array('path' => '/baz'),
            )),
            array(array(
                'given-json' => '{"foo":["bar","qux","baz"]}',
                'expected-json' => '{"foo":["bar","baz"]}',
                'remove-operation' => (object) array('path' => '/foo/1'),
            )),
            array(array(
                'given-json' => '{"foo":["bar","qux","zoo","baz"]}',
                'expected-json' => '{"foo":["bar","qux","zoo"]}',
                'remove-operation' => (object) array('path' => '/foo/-'),
            )),
            array(array(
                'given-json' => '{"baz":{"boo":{"bar":"zoo"}}}',
                'expected-json' => '{"baz":{"boo":{}}}',
                'remove-operation' => (object) array('path' => '/baz/boo/bar'),
            )),
            array(array(
                'given-json' => '["done", "started", "planned", "pending", "archived"]',
                'expected-json' => '["done", "started", "pending", "archived"]',
                'remove-operation' => (object) array('path' => '/2'),
            )),
        );
    }
}
