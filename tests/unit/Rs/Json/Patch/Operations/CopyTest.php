<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\Operations;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @expectedExceptionMessage Mandatory from property not set
     */
    public function missingManadatoryOperationKeyShouldThrowExpectedException()
    {
        $operation = new \stdClass;
        $operation->to = '/a/b/d';
        $operation->path = '/a/b/e';

        $moveOperation = new Copy($operation);
    }
    /**
     * @test
     */
    public function targetDocumentIsReturnedUnmodifiedOnNonexistentFromPointer()
    {
        $targetJson = $expectedJson = '{"a":{"b":{"c":17}}}';
        $operation = new \stdClass;
        $operation->from = '/a/b/d';
        $operation->path = '/a/b/c';

        $moveOperation = new Copy($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $moveOperation->perform($targetJson)
        );
    }
    /**
     * @test
     * @dataProvider copyProvider
     */
    public function shouldCopyAsExcepected($providerData)
    {
        $targetJson = $providerData['given-json'];
        $expectedJson = $providerData['expected-json'];
        $operation = $providerData['copy-operation'];

        $moveOperation = new Copy($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $moveOperation->perform($targetJson)
        );
    }

    /**
     * @return array
     */
    public function copyProvider()
    {
        return array(
            array(array(
                'given-json' => '{"a":{"b":{"c":17}}}',
                'expected-json' => '{"a":{"b":{"c":17},"d":17}}',
                'copy-operation' => (object) array('from' => '/a/b/c', 'path' => '/a/d'),
            )),
            array(array(
                'given-json' => '{"a":{"b":{"c":17}}}',
                'expected-json' => '{"a":{"b":{"c":17}}}',
                'copy-operation' => (object) array('from' => '/a/b/c', 'path' => '/a/b/c'),
            )),
            array(array(
                'given-json' => '{"a":{"b":["c","d","e"]}}',
                'expected-json' => '{"a":{"b":["c","d","e"],"d":"c"}}',
                'copy-operation' => (object) array('from' => '/a/b/0', 'path' => '/a/d'),
            )),
            array(array(
                'given-json' => '{"a":{"b":["c","d","e"]}}',
                'expected-json' => '{"a":{"b":["c","d","e"],"d":"e"}}',
                'copy-operation' => (object) array('from' => '/a/b/-', 'path' => '/a/d'),
            )),
            array(array(
                'given-json' => '{"a":{"b":["c","d","e"],"d":["f","g"]}}',
                'expected-json' => '{"a":{"b":["c","d","e"],"d":["f","g","e"]}}',
                'copy-operation' => (object) array('from' => '/a/b/-', 'path' => '/a/d/-'),
            )),
        );
    }
}
