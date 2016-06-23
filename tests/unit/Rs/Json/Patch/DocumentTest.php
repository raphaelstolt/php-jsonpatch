<?php
namespace Rs\Json\Patch;

use Rs\Json\Patch\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @expectedExceptionMessage Unable to extract patch operations from '
     * @dataProvider emptyPatchDocumentProvider
     */
    public function shouldThrowExpectedExceptionOnEmptyPatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @expectedExceptionMessage No
     * @dataProvider nonePatchDocumentProvider
     */
    public function shouldThrowExpectedExceptionOnNonePatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
    }
    /**
     * @test
     * @dataProvider addPatchDocumentProvider
     */
    public function shouldReturnAnArrayOfAddPatchOperationsOnAddPatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Add',
            $patchOperations
        );
    }
    /**
     * @test
     */
    public function shouldReturnAnEmptyArrayOfPatchOperations()
    {
        $patchDocument = '[{"op":"foo", "path":"/a/b"}]';
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(0, $patchOperations);
        $this->assertEmpty($patchOperations);
    }
    /**
     * @test
     * @dataProvider copyPatchDocumentProvider
     */
    public function shouldReturnAnArrayOfCopyPatchOperationsOnCopyPatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Copy',
            $patchOperations
        );
    }
    /**
     * @test
     * @dataProvider movePatchDocumentProvider
     */
    public function shouldReturnAnArrayOfMovePatchOperationsOnMovePatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Move',
            $patchOperations
        );
    }
    /**
     * @test
     * @dataProvider removePatchDocumentProvider
     */
    public function shouldReturnAnArrayOfRemovePatchOperationsOnRemovePatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Remove',
            $patchOperations
        );
    }
    /**
     * @test
     * @dataProvider replacePatchDocumentProvider
     */
    public function shouldReturnAnArrayOfReplacePatchOperationsOnReplacePatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Replace',
            $patchOperations
        );
    }
    /**
     * @test
     * @dataProvider testPatchDocumentProvider
     */
    public function shouldReturnAnArrayOfTestPatchOperationsOnTestPatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(2, $patchOperations);
        $this->assertContainsOnlyInstancesOf(
            'Rs\Json\Patch\Operations\Test',
            $patchOperations
        );
    }
    /**
     * @test
     * @dataProvider patchDocumentProvider
     */
    public function shouldReturnAnArrayOfPatchOperationsOnPatchDocument($patchDocument)
    {
        $document = new Document($patchDocument);
        $patchOperations = $document->getPatchOperations();

        $this->assertCount(6, $patchOperations);

        $operationNames = array(
            'test',
            'remove',
            'add',
            'replace',
            'move',
            'copy',
        );
        
        foreach ($operationNames as $index => $operationName) {
            $this->assertInstanceOf(
                'Rs\Json\Patch\Operations\\' . ucfirst($operationName),
                $patchOperations[$index]
            );
        }
    }
    /**
     * @test
     * @expectedException Rs\Json\Patch\InvalidOperationException
     * @dataProvider invalidPatchDocumentProvider
     */
    public function shouldThrowExpectedExceptionOnInvalidPatchDocument($invalidPatchDocument)
    {
        $document = new Document(json_encode($invalidPatchDocument));
    }

    /**
     * @return array
     */
    public function invalidPatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'test', 'path' => '/a/b/c', 'valuer' => 'foo'),
                array('op' => 'test', 'patho' => '/a/b/c', 'value' => 'foo'),
                array('op' => 'remove', 'patho' => '/a/b/c'),
                array('op' => 'add', 'path' => '/a/b/c', 'valuer' => array('foo', 'bar')),
                array('op' => 'add', 'patho' => '/a/b/c', 'value' => array('foo', 'bar')),
                array('op' => 'replace', 'path' => '/a/b/c', 'valuer' => array('foo', 'bar')),
                array('op' => 'replace', 'patho' => '/a/b/c', 'value' => array('foo', 'bar')),
                array('op' => 'move', 'from' => '/a/b/c', 'patho' => '/a/b'),
                array('op' => 'move', 'fromo' => '/a/b/c', 'path' => '/a/b'),
                array('op' => 'copy', 'from' => '/a/b/c', 'patho' => '/a/b'),
                array('op' => 'copy', 'fromo' => '/a/b/c', 'path' => '/a/b'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function addPatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'add', 'path' => '/a/b/c', 'value' => array('foo', 'bar')),
                array('path' => '/d/e/f', 'op' => 'add', 'value' => array('baz', 'bbz')),
            ))),
        );
    }
    /**
     * @return array
     */
    public function copyPatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'copy', 'path' => '/a/b/c', 'from' => '/foo/bar'),
                array('path' => '/d/e/f', 'op' => 'copy', 'from' => '/baz/bbz'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function movePatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'move', 'path' => '/a/b/c', 'from' => '/foo/bar'),
                array('path' => '/d/e/f', 'op' => 'move', 'from' => '/baz/bbz'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function removePatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'remove', 'path' => '/a/b/c'),
                array('path' => '/d/e/f', 'op' => 'remove'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function replacePatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'replace', 'path' => '/a/b/c', 'value' => 42),
                array('path' => '/d/e/f', 'value' => array('one', 'two'), 'op' => 'replace'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function patchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'test', 'path' => '/a/b/c', 'value' => 'foo'),
                array('op' => 'remove', 'path' => '/a/b/c'),
                array('value' => array('foo', 'bar'), 'path' => '/a/b/c', 'op' => 'add'),
                array('op' => 'replace', 'path' => '/a/b/c', 'value' => 42),
                array('op' => 'move', 'from' => '/a/b/d', 'path' => '/a/b/c'),
                array('op' => 'copy', 'path' => '/a/b/d', 'from' => '/a/b/e'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function testPatchDocumentProvider()
    {
        return array(
            array(json_encode(array(
                array('op' => 'test', 'path' => '/a/b/c', 'value' => 'foo'),
                array('op' => 'test', 'path' => '/a/b/c', 'value' => 'bar'),
            ))),
        );
    }
    /**
     * @return array
     */
    public function emptyPatchDocumentProvider()
    {
        return array(
            array(json_encode('')),
            array(json_encode(" ")),
            array(json_encode(null)),
            array(json_encode(false)),
            array(json_encode(array())),
            array(json_encode(0)),
            array(json_encode(17)),
            array(json_encode('0')),
        );
    }
    /**
     * @return array
     */
    public function nonePatchDocumentProvider()
    {
        return array(
            array(json_encode(array(array('one' => 1, 'two' => 2)))),
            array(json_encode(array(array('mop' => 'test', 'path' => '/a/b/c')))),
        );
    }
}
