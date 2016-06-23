<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchMoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldMoveAsExpected()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[ {"op":"move", "from":"/a/b/-", "path":"/a/b/0"} ]';
        $expectedDocument = '{"a":{"b":["e","c","d"]}}';

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
    public function shouldMoveAsExpectedInBatchOfPatches()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[
          {"op":"add", "path":"/a/d", "value":["a","b"]},
          {"op":"test", "path":"/a/d/-", "value":"b"},
          {"op":"remove", "path":"/a/d/-"},
          {"op":"test", "path":"/a/d/-", "value":"a"},
          {"op":"add", "path":"/a/d/-", "value":"b"},
          {"op":"test", "path":"/a/d", "value":["a","b"]},
          {"op":"move", "path":"/a/c", "from":"/a/d"},
          {"op":"test", "path":"/a/c", "value":["a","b"]}
        ]';

        $expectedDocument = '{"a":{"b":["c","d","e"],"c":["a","b"]}}';

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
    public function shouldReturnTargetDocumentWhenFromPointerIsNonexistent()
    {
        $expectedDocument = $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[ {"op":"copy", "from":"/baz", "path":"/foo/boo"} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}
