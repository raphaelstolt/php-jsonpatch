<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchCopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldCopyAsExpected()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[ {"op":"copy", "from":"/a/b/-", "path":"/a/d"} ]';
        $expectedDocument = '{"a":{"b":["c","d","e"],"d":"e"}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     */
    public function shouldCopyAsExpectedInBatchOfPatches()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[
          {"op":"add", "path":"/a/d", "value":["a","b"]},
          {"op":"test", "path":"/a/d/-", "value":"b"},
          {"op":"copy", "from":"/a/b/-", "path":"/a/d/-"},
          {"op":"test", "path":"/a/d/-", "value":"e"}
        ]';

        $expectedDocument = '{"a":{"b":["c","d","e"],"d":["a","b","e"]}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}