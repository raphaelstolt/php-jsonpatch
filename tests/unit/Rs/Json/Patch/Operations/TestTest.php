<?php
namespace Rs\Json\Patch\Operations;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operations;

class TestTest extends TestCase
{
    /**
     * @test
     */
    public function missingManadatoryOperationKeyShouldThrowExpectedException()
    {
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('Mandatory value property not set');

        $operation = new \stdClass;
        $operation->path = '/a/b/d';
        $operation->valuer = 'foo';

        $testOperation = new Test($operation);
    }

    /**
     * @test
     * @dataProvider matchingObjectProvider
     */
    public function shouldReturnTrueForMatchingObjects($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertTrue($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider noneMatchingPointerProvider
     */
    public function shouldReturnFalseForNoneMatchingPointer($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertFalse($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider matchingStringProvider
     */
    public function shouldReturnTrueForMatchingStrings($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertTrue($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider noneMatchingStringProvider
     */
    public function shouldReturnFalseForNoneMatchingStrings($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertFalse($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider matchingNumberProvider
     */
    public function shouldReturnTrueForMatchingNumbers($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertTrue($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider noneMatchingNumberProvider
     */
    public function shouldReturnFalseForNoneMatchingNumbers($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertFalse($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider matchingLiteralProvider
     */
    public function shouldReturnTrueForMatchingLiterals($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertTrue($testOperation->perform($targetJson));
    }

    /**
     * @test
     * @dataProvider noneMatchingLiteralProvider
     */
    public function shouldReturnFalseForNoneMatchingLiterals($providerData)
    {
        $targetJson = $providerData['given-json'];
        $operation = $providerData['test-operation'];

        $testOperation = new Test($operation);

        $this->assertFalse($testOperation->perform($targetJson));
    }

    /**
     * @test
     */
    public function shouldReturnTrueForMatchingArrays()
    {
        $operation = new \stdClass;
        $operation->path = '/chars/array';
        $operation->value = array("abc", "def");

        $testOperation = new Test($operation);

        $this->assertTrue($testOperation->perform('{"chars":{"array":["abc","def"]}}'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseForNoneMatchingArrays()
    {
        $operation = new \stdClass;
        $operation->path = '/chars/array';
        $operation->value = array("def", "hij");

        $testOperation = new Test($operation);

        $this->assertFalse($testOperation->perform('{"chars":{"array":["abc","def"]}}'));
    }

    /**
     * @return array
     */
    public function noneMatchingLiteralProvider()
    {
        return array(
            array(array(
                'given-json' => '{"literal":{"false":false}}',
                'test-operation' => (object) array('path' => '/literal/false', 'value' => true),
            )),
            array(array(
                'given-json' => '{"literal":{"true":true}}',
                'test-operation' => (object) array('path' => '/literal/true', 'value' => false),
            )),
            array(array(
                'given-json' => '{"literal":{"null":null}}',
                'test-operation' => (object) array('path' => '/literal/null', 'value' => 0),
            )),
        );
    }

    /**
     * @return array
     */
    public function matchingLiteralProvider()
    {
        return array(
            array(array(
                'given-json' => '{"literal":{"false":false}}',
                'test-operation' => (object) array('path' => '/literal/false', 'value' => false),
            )),
            array(array(
                'given-json' => '{"literal":{"true":true}}',
                'test-operation' => (object) array('path' => '/literal/true', 'value' => true),
            )),
            array(array(
                'given-json' => '{"literal":{"null":null}}',
                'test-operation' => (object) array('path' => '/literal/null', 'value' => null),
            )),
        );
    }

    /**
     * @return array
     */
    public function noneMatchingNumberProvider()
    {
        return array(
            array(array(
                'given-json' => '{"number":{"seventeen":17}}',
                'test-operation' => (object) array('path' => '/number/seventeen', 'value' => 16),
            )),
            array(array(
                'given-json' => '{"number":{"seventeentwenty":17.20}}',
                'test-operation' => (object) array('path' => '/number/seventeentwenty', 'value' => 16.20),
            )),
            array(array(
                'given-json' => '{"number":{"nil":0}}',
                'test-operation' => (object) array('path' => '/number/nil', 'value' => 0.0),
            )),
        );
    }

    /**
     * @return array
     */
    public function matchingNumberProvider()
    {
        return array(
            array(array(
                'given-json' => '{"number":{"seventeen":17}}',
                'test-operation' => (object) array('path' => '/number/seventeen', 'value' => 17),
            )),
            array(array(
                'given-json' => '{"number":{"seventeentwenty":17.20}}',
                'test-operation' => (object) array('path' => '/number/seventeentwenty', 'value' => 17.20),
            )),
            array(array(
                'given-json' => '{"number":{"nil":0}}',
                'test-operation' => (object) array('path' => '/number/nil', 'value' => 0),
            )),
        );
    }

    /**
     * @return array
     */
    public function noneMatchingStringProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":"bar"}',
                'test-operation' => (object) array('path' => '/foo', 'value' => 'baz'),
            )),
            array(array(
                'given-json' => '{"foo":["a","b","c","d"]}',
                'test-operation' => (object) array('path' => '/foo/2', 'value' => 'd'),
            )),
            array(array(
                'given-json' => '{"chars":{"array":["abc","def", " "]}}',
                'test-operation' => (object) array('path' => '/chars/array/2', 'value' => ''),
            )),
        );
    }

    /**
     * @return array
     */
    public function matchingStringProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":"bar"}',
                'test-operation' => (object) array('path' => '/foo', 'value' => 'bar'),
            )),
            array(array(
                'given-json' => '{"foo":["a","b","c","d"]}',
                'test-operation' => (object) array('path' => '/foo/3', 'value' => 'd'),
            )),
            array(array(
                'given-json' => '{"chars":{"array":["abc","def"]}}',
                'test-operation' => (object) array('path' => '/chars/array/-', 'value' => 'def'),
            )),
            array(array(
                'given-json' => '{"chars":{"array":["abc","def"]}}',
                'test-operation' => (object) array('path' => '/chars/array/1', 'value' => 'def'),
            )),
            array(array(
                'given-json' => '{"chars":{"array":["abc","def", ""]}}',
                'test-operation' => (object) array('path' => '/chars/array/2', 'value' => ''),
            )),
        );
    }

    /**
     * @return array
     */
    public function noneMatchingPointerProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":"bar"}',
                'test-operation' => (object) array('path' => '/boo', 'value' => 'bar'),
            )),
            array(array(
                'given-json' => '{"foo":"bar"}',
                'test-operation' => (object) array('path' => '/foo/boo', 'value' => 'bar'),
            )),
            array(array(
                'given-json' => '{"q":{"bar":2}}',
                'test-operation' => (object) array('path' => '/a/b', 'value' => 100),
            )),
        );
    }

    /**
     * @return array
     */
    public function matchingObjectProvider()
    {
        return array(
            array(array(
                'given-json' => '{"foo":"bar"}',
                'test-operation' => (object) array('path' => '', 'value' => array("foo" => "bar")),
            )),
            array(array(
                'given-json' => '{"foo":{"coo":{"koo":"roo","moo":"zoo"}}}',
                'test-operation' => (object) array('path' => '/foo/coo', 'value' => array("koo" => "roo", "moo" => "zoo")),
            )),
            array(array(
                'given-json' => '{"foo":"123"}',
                'test-operation' => (object) array('path' => '/foo', 'value' => "123"),
            ))
        );
    }
}
