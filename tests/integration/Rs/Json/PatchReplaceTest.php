<?php
namespace Rs\Json;

use Rs\Json\Patch;

class PatchReplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReplaceAsExpected()
    {
        $targetDocument = '{"a":{"b":["c","d","e"]}}';
        $patchDocument = '[ {"op":"replace", "path":"/a/b/0", "value":"cc"} ]';
        $expectedDocument = '{"a":{"b":["cc","d","e"]}}';

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
    public function shouldReplaceAsExpectedInBatchOfPatches()
    {
        $targetDocument = '{"a":{"b":"dev"}}';
        $patchDocument = '[
          {"op":"add", "path":"/a/d", "value":"prod"},
          {"op":"test", "path":"/a/d", "value":"prod"},
          {"op":"move", "path":"/a/d", "from":"/a/b"},
          {"op":"test", "path":"/a/d", "value":"dev"},
          {"op":"replace", "path":"/a/d", "value":"dev1"},
          {"op":"test", "path":"/a/d", "value":"dev1"}
        ]';

        $expectedDocument = '{"a":{"d":"dev1"}}';

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
        $patchDocument = '[ {"op":"replace", "path":"/baz", "value":"moo"} ]';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
    /**
     * @test
     * @ticket 21 (https://github.com/raphaelstolt/php-jsonpatch/issues/21)
     */
    public function shouldKeepEncoding()
    {
        $targetDocument = '{"bookid": "1","bookname": "第二个"}';
        $patchDocument = '[
          {"op":"replace", "path":"/bookid", "value":"0"},
          {"op":"replace", "path":"/bookname", "value":"第一个"}
        ]';

        $expectedDocument = '{"bookid": "0", "bookname":"第一个"}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }

    /**
     * @test
     * @ticket 30 (https://github.com/raphaelstolt/php-jsonpatch/issues/30)
     */
    public function shouldPreserveObjects()
    {
        $targetDocument = '{"foo":{"obj": {"property": "value", "emptyObj": {}}}}';
        $patchDocument = '[{"op":"replace", "path":"/foo/obj/property", "value":"qux"}]';
        $expectedDocument = '{"foo":{"obj": {"property": "qux", "emptyObj": {}}}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}
