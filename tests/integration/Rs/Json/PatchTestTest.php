<?php
namespace Rs\Json;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch;
use Rs\Json\Patch\FailedTestException;
use Rs\Json\Pointer\InvalidPointerException;

class PatchTestTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowFailedTestExceptionWhenTestFails()
    {
        $this->expectException(FailedTestException::class);
        $this->expectExceptionMessage('Failed on Test PatchOperation at index:');

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
     */
    public function shouldThrowFailedTestExceptionWhenTestFailsForPriorPatch()
    {
        $this->expectException(FailedTestException::class);
        $this->expectExceptionMessage('Failed on Test PatchOperation at index:');

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
     */
    public function shouldThrowFailedTestExceptionWhenUsingPointerEscapes()
    {
        $this->expectException(FailedTestException::class);
        $this->expectExceptionMessage('Failed on Test PatchOperation at index:');

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
     */
    public function shouldThrowFailedTestExceptionWhenTestFailsForUnsuccessfulComparison()
    {
        $this->expectException(FailedTestException::class);
        $this->expectExceptionMessage('Failed on Test PatchOperation at index:');

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
     */
    public function shouldThrowExceptionOnUsageOfUriFragmentIdentifierInPatch()
    {
        $this->expectException(InvalidPointerException::class);
        $this->expectExceptionMessage('Pointer starts with invalid character');

        $targetDocument = '{"foo":"bar"}';
        $patchDocument = '[{"op":"add", "path":"#/baz", "value":[1,2,3]}]';
        $expectedDocument = '{"foo":"bar","baz":[1,2,3]}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();
    }
}
