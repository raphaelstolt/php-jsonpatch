<?php
namespace Rs\Json\Patch\Operations;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operations;

class MoveTest extends TestCase
{
    /**
     * @test
     */
    public function missingManadatoryOperationKeyShouldThrowExpectedException()
    {
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('Mandatory from property not set');

        $operation = new \stdClass;
        $operation->to = '/a/b/c';
        $operation->path = '/a/b/d';

        $moveOperation = new Move($operation);
    }

    /**
     * @test
     */
    public function targetDocumentIsReturnedUnmodifiedOnNonexistentFromPointer()
    {
        $targetJson = $expectedJson = '{"a":{"b":{"cc":17}}}';

        $operation = new \stdClass;
        $operation->from = '/a/b/c';
        $operation->path = '/a/b/d';

        $moveOperation = new Move($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $moveOperation->perform($targetJson)
        );
    }

    /**
     * @test
     * @dataProvider moveProvider
     */
    public function shouldMoveAsExcepected($providerData)
    {
        $targetJson = $providerData['given-json'];
        $expectedJson = $providerData['expected-json'];
        $operation = $providerData['move-operation'];

        $moveOperation = new Move($operation);

        $this->assertJsonStringEqualsJsonString(
            $expectedJson,
            $moveOperation->perform($targetJson)
        );
    }

    /**
     * @return array
     */
    public function moveProvider()
    {
        return array(
            array(array(
                'given-json' => '{"a":{"b":{"c":17}}}',
                'expected-json' => '{"a":{"b":{"d":17}}}',
                'move-operation' => (object) array('from' => '/a/b/c', 'path' => '/a/b/d'),
            )),
            array(array(
                'given-json' => '{"a":{"b":{"c":17}}}',
                'expected-json' => '{"a":{"b":{"c":17}}}',
                'move-operation' => (object) array('from' => '/a/b/c', 'path' => '/a/b/c'),
            )),
            array(array(
                'given-json' => '{"a":["one","two"]}',
                'expected-json' => '{"b":["one","two"]}',
                'move-operation' => (object) array('from' => '/a', 'path' => '/b'),
            )),
            array(array(
                'given-json' => '{"a":["one","two"]}',
                'expected-json' => '{"a":["one"],"b":"two"}',
                'move-operation' => (object) array('from' => '/a/-', 'path' => '/b'),
            )),
        );
    }
}
