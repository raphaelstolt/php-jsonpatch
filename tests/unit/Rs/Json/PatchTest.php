<?php
namespace Rs\Json;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;

class PatchTest extends TestCase
{
    /**
     * @test
     */
    public function shouldHaveExpectedMediaTypeDefined()
    {
        $this->assertEquals('application/json-patch+json', Patch::MEDIA_TYPE);
    }

    /**
     * @test
     * @dataProvider invalidJsonProvider
     */
    public function shouldThrowExpectedExceptionOnInvalidTargetDocument($invalidJson)
    {
        $this->expectException(InvalidTargetDocumentJsonException::class);
        $this->expectExceptionMessage('Cannot operate on invalid Json.');

        $patch = new Patch($invalidJson, '{"op":"test", "path":"/a/b/c", "value":"foo"}');
    }

    /**
     * @test
     * @dataProvider invalidJsonProvider
     */
    public function shouldThrowExpectedExceptionOnInvalidPatchDocument($invalidJson)
    {
        $this->expectException(InvalidPatchDocumentJsonException::class);
        $this->expectExceptionMessage('Cannot operate on invalid Json.');

        $patch = new Patch('{"a":"foo"}', $invalidJson);
    }

    /**
     * @return array
     */
    public function invalidJsonProvider()
    {
        return array(
          array('['),
          array('{'),
          array('{}}'),
          array('{"Missing colon" null}'),
        );
    }
}
