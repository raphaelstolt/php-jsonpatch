<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException Rs\Json\Patch\InvalidTargetDocumentJsonException
     * @expectedExceptionMessage Cannot operate on invalid Json.
     * @dataProvider invalidJsonProvider
     */
    public function shouldThrowExpectedExceptionOnInvalidTargetDocument($invalidJson)
    {
        $patch = new Patch($invalidJson, '{"op":"test", "path":"/a/b/c", "value":"foo"}');
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidPatchDocumentJsonException
     * @expectedExceptionMessage Cannot operate on invalid Json.
     * @dataProvider invalidJsonProvider
     */
    public function shouldThrowExpectedExceptionOnInvalidPatchDocument($invalidJson)
    {
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