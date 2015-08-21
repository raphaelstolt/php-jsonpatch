<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchTestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Rs\Json\Patch\FailedTestException
     * @expectedExceptionMessage Failed on Test PatchOperation at index:
     */
    public function shouldThrowFailedTestExceptionWhenTestFails()
    {
        $expectedDocument = $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[
          {"op":"test", "path":"/baz", "value":"qux"},
          {"op":"add", "path":"/baz", "value":"qux"}
        ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\FailedTestException
     * @expectedExceptionMessage Failed on Test PatchOperation at index:
     */
    public function shouldThrowFailedTestExceptionWhenTestFailsForPriorPatch()
    {
        $expectedDocument = $targetDocument = '{"a":{"b":{"c": 100}}}';
        $patchDocument = '[
          {"op":"replace", "path":"/a/b/c", "value":42 },
          {"op":"test", "path":"/a/b/c", "value":"C" }
        ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\FailedTestException
     * @expectedExceptionMessage Failed on Test PatchOperation at index:
     */
    public function shouldThrowFailedTestExceptionWhenUsingPointerEscapes()
    {
        $expectedDocument = $targetDocument = '{"/": 9, " ~1": 10}';
        $patchDocument = '[ {"op":"test", "path":"/~01", "value": 10} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\FailedTestException
     * @expectedExceptionMessage Failed on Test PatchOperation at index:
     */
    public function shouldThrowFailedTestExceptionWhenTestFailsForUnsuccessfulComparison()
    {
        $expectedDocument = $targetDocument = '{"/": 9, " ~1": 10}';
        $patchDocument = '[ {"op":"test", "path":"/~01", "value":"10"} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @ticket 8 (https://github.com/raphaelstolt/php-jsonpatch/issues/8)
     */
    public function shouldDoASuccessfulTestComparison()
    {
        $expectedDocument = $targetDocument = '{"arrayField": [{"name":"foo"}, {"name":"bar"}]}';
        $patchDocument = '[ {"op":"test", "path":"/arrayField/0", "value":{"name":"foo"}} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @expectedException Rs\Json\Pointer\InvalidPointerException
     * @expectedExceptionMessage Pointer starts with invalid character
     */
    public function shouldThrowExceptionOnUsageOfUriFragmentIdentifierInPatch()
    {
        $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[{"op":"add", "path":"#/baz", "value":[1,2,3]}]';
        $expectedDocument = '{"foo":"bar","baz":[1,2,3]}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();
    }
}
