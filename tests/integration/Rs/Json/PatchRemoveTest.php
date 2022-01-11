<?php
namespace Rs\Json;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch;

class PatchRemoveTest extends TestCase
{
    /**
     * @return array
     */
    public function removeNullProvider()
    {
        return array(
            array(
                '{"a":{"b":null}}', // target document
                '[ {"op":"remove", "path":"/a/b"} ]', // patch document
                '{"a":{}}' // expected document
            ),
            array(
                '{"a":null}',
                '[ {"op":"remove", "path":"/a"} ]',
                '{}'
            ),
            array(
                '{"a":{"b":["c","d",null]}}',
                '[ {"op":"remove", "path":"/a/b/-"} ]',
                '{"a":{"b":["c","d"]}}'
            ),
        );
    }

    /**
     * @param string $targetDocument
     * @param string $patchDocument
     * @param string $expectedDocument
     *
     * @dataProvider removeNullProvider
     * @test
     * @ticket 2
     */
    public function shouldRemoveNullValueAsExpected(
        $targetDocument,
        $patchDocument,
        $expectedDocument
    ) {
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
    /**
     * @test
     * @ticket 35 (https://github.com/raphaelstolt/php-jsonpatch/issues/35)
     */
    public function shouldPreserveEmptyObject()
    {
        $targetDocument = '{"foo":{"bar":{"baz": {}, "qux": "val"}}, "bar": {}}';
        $patchDocument = '[{"op":"remove", "path":"/foo/bar/qux"}]';
        $expectedDocument = '{"foo":{"bar":{"baz": {}}}, "bar": {}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @ticket 35 (https://github.com/raphaelstolt/php-jsonpatch/issues/35)
     */
    public function shouldPreserveEmptyObjectNumericObjectProperties()
    {
        $targetDocument = '{"foo":{"bar":{"baz": {}, "3": "val"}}, "bar": {}}';
        $patchDocument = '[{"op":"remove", "path":"/foo/bar/3"}]';
        $expectedDocument = '{"foo":{"bar":{"baz": {}}}, "bar": {}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}
