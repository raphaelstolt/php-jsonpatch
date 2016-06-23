<?php
namespace Rs\Json\Patch;

use Rs\Json\Patch\Operations\Add;
use Rs\Json\Patch\Operations\Remove;
use Rs\Json\Patch\Operations\Replace;

class OperationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @expectedExceptionMessage No path property set for patch operation
     */
    public function shouldThrowExpectedExceptionOnMissingPath()
    {
        $operation = new \stdClass;
        $operation->value = array('foo', 'bar');
        $addOperation = new Add($operation);
    }
    /**
     * @test
     */
    public function shouldSetExpectedProperties()
    {
        $operation = new \stdClass;
        $operation->value = array('foo', 'bar');
        $operation->path = '/a/b/c';

        $replaceOperation = new Replace($operation);

        $this->assertEquals('replace', $replaceOperation->getName());
        $this->assertEquals($operation->path, $replaceOperation->getPath());
        $this->assertEquals($operation->value, $replaceOperation->getValue());
    }
    /**
     * @test
     */
    public function shouldSetValueToNullOnOperationWithNoValue()
    {
        $operation = new \stdClass;
        $operation->path = '/a/b/c';

        $removeOperation = new Remove($operation);

        $this->assertEquals(null, $removeOperation->getValue());
    }
}
