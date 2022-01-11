<?php
namespace Rs\Json\Patch;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operations\Add;
use Rs\Json\Patch\Operations\Remove;
use Rs\Json\Patch\Operations\Replace;

class OperationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExpectedExceptionOnMissingPath()
    {
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessage('No path property set for patch operation');

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
