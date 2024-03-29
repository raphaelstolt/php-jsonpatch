<?php
namespace Rs\Json;

use PHPUnit\Framework\TestCase;
use Rs\Json\Patch;

class PatchAddTest extends TestCase
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
     * @ticket 28 (https://github.com/raphaelstolt/php-jsonpatch/issues/28)
     */
    public function shouldAddEmptyObject()
    {
        $targetDocument = '{"foo":{"obj": {"property": "value", "emptyObj": {}}}}';
        $patchDocument = '[{"op":"add", "path":"/foo/obj/anotherProp", "value":"qux"}]';
        $expectedDocument = '{"foo":{"obj": {"property": "value", "anotherProp": "qux", "emptyObj": {}}}}';

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
    public function shouldAllowPathAsAnArray()
    {
        $targetDocument = '{"foo":{"obj": {"property": "value", "emptyObj": {}}}}';
        $patchDocument = '[{"op":"add", "path":["foo", "obj", "anotherProp"], "value":"qux"}]';
        $expectedDocument = '{"foo":{"obj": {"property": "value", "anotherProp": "qux", "emptyObj": {}}}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }


    /**
     * @test
     * @ticket 33 (https://github.com/raphaelstolt/php-jsonpatch/issues/33)
     */
    public function shouldPreserveEmptyObjectSameLevel()
    {
        $targetDocument = '{"foo": {"bar": {"baz": {"boo": {}, "qux": "value"}}}}';
        $patchDocument = '[{"op":"add", "path":"/foo/bar/baz/qux", "value":"otherValue"}]';
        $expectedDocument = '{"foo": {"bar": {"baz": {"boo": {}, "qux": "otherValue"}}}}';

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
    /**
     * @test
     */
    public function shouldRespectDeepPointers()
    {
        $targetDocument = '{
          "authority": {
            "pd": [],
            "od": [],
            "md": [],
            "custom": [
              {
                "text": "I should be able to edit this, right?",
                "type": "with_approval"
              },
              {
                "text": "Test this please.",
                "type": "complete_authority"
              },
              {
                "text": "dfsasdasdf",
                "type": "with_approval"
              }
            ]
          }
        }';

        $patchDocument = '[
            {"op": "add", "path":"/authority/custom/3", "value": {"text": "some-test-text","type": "with_approval_test"}}
        ]';

        $expectedDocument = '{
          "authority": {
            "pd": [],
            "od": [],
            "md": [],
            "custom": [
              {
                "text": "I should be able to edit this, right?",
                "type": "with_approval"
              },
              {
                "text": "Test this please.",
                "type": "complete_authority"
              },
              {
                "text": "dfsasdasdf",
                "type": "with_approval"
              },
              {
                "text": "some-test-text",
                "type": "with_approval_test"
              }
            ]
          }
        }';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }

    /**
     * @test
     * @ticket 10 (https://github.com/raphaelstolt/php-jsonpatch/issues/10)
     */
    public function shouldAddNotReplace()
    {
        $targetDocument = '[
            {"foo": "alpha"},
            {"foo": "beta"},
            {"foo": "gamma"}
        ]';

        $patchDocument = '[
            {"op": "add", "path": "/1", "value": {"foo": "beta2"}},
            {"op": "test", "path": "/2", "value": {"foo": "beta"}},
            {"op": "remove", "path": "/2"}
        ]';

        $expectedDocument = '[
            {"foo": "alpha"},
            {"foo": "beta2"},
            {"foo": "gamma"}
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
    public function shouldAddToSingleElementArray()
    {
        $targetDocument = '[
            {"foo": "alpha"}
        ]';

        $patchDocument = '[
            {"op": "add", "path": "/1", "value": {"foo": "beta"}}
        ]';

        $expectedDocument = '[
            {"foo": "alpha"},
            {"foo": "beta"}
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
     * @ticket 37 (https://github.com/raphaelstolt/php-jsonpatch/issues/37)
     */
    public function shouldCorrectlyUseNumericIndexInObjectHandling()
    {
        $targetDocument = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"qux"} ] }}}';
        $patchDocument = '[{"op":"add", "path":"/foo/bar/baz/1", "value":{"bar":"otherValue"} }]';
        $expectedDocument = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"otherValue"}, {"bar":"qux"} ] }}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }

    /**
     * @test
     * @ticket 37 (https://github.com/raphaelstolt/php-jsonpatch/issues/37)
     */
    public function shouldCorrectlyUseNumericIndexInObjectHandlingWithAddedSubProp()
    {
        $targetDocument = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"qux"} ] }}}';
        $patchDocument = '[{"op":"add", "path":"/foo/bar/baz/1/bar", "value":"otherValue"}]';
        $expectedDocument = '{"foo": {"bar": {"baz": [ {"bar":"baz"}, {"bar":"otherValue"} ] }}}';

        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }
}
