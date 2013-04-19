<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchRemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldRemoveAsExpected()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[ {"op":"remove", "path":"/a/b/-"} ]';
        $expectedDocument = '{"a":{"b":["c","d"]}}';

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
          {"op":"add", "path":"/a/d/-", "value":"c"},
          {"op":"test", "path":"/a/d/-", "value":"c"},
          {"op":"remove", "path":"/a/d/-"},
          {"op":"test", "path":"/a/d/-", "value":"b"}
        ]';

        $expectedDocument = '{"a":{"b":["c","d","e"],"d":["a","b"]}}';

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
    public function shouldReturnTargetDocumentWhenPathPointerIsNonexistent()
    {
        $expectedDocument = $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[ {"op":"remove", "path":"/baz/boo"} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}