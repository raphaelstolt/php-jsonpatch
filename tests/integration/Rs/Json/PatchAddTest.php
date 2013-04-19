<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchAddTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAddAsExpected()
    {
        $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[{"op":"add", "path":"/baz", "value":"qux", "xyz":123}]';
        $expectedDocument = '{"foo":"bar","baz":"qux"}';

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
    public function shouldAddNestedObjectMemberAsExpected1()
    {
        $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[ {"op":"add", "path":"/child", "value": { "grandchild" : { } }} ]';
        $expectedDocument = '{"foo":"bar", "child":{"grandchild":{}}}';

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
    public function shouldReturnTargetDocumentWhenPatchNotApplicable()
    {
        $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[ {"op":"add", "path":"/baz/bat", "value":"qux"} ]';
        $expectedDocument = '{"foo":"bar"}';

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
    public function shouldAddAsExpectedInBatchOfPatches()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[
          {"op":"add", "path":"/a/d", "value":["a","b"]},
          {"op":"test", "path":"/a/d/-", "value":"b"},
          {"op":"remove", "path":"/a/d/-"},
          {"op":"test", "path":"/a/d/-", "value":"a"},
          {"op":"add", "path":"/a/d/-", "value":"b"},
          {"op":"test", "path":"/a/d", "value":["a","b"]}
        ]';

        $expectedDocument = '{"a":{"b":["c","d","e"],"d":["a","b"]}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}