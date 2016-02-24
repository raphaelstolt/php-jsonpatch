<?php

use Rs\Json\Patch;
use Rs\Json\Patch\FailedTestException;
use Rs\Json\Patch\InvalidOperationException;

class SpecTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider nonErrorTestsProvider
     *
     * @param $targetDocument
     * @param $patchDocument
     * @param $expectedDocument
     */
    public function itSucceedsAllNormalTests($targetDocument, $patchDocument, $expectedDocument)
    {
        // Arrange

        // Act
        $patch = new Patch($targetDocument, $patchDocument);
        $patchedDocument = $patch->apply();

        // Assert
        if (!$expectedDocument) {
            return;
        }

        $this->assertJsonStringEqualsJsonString(
            $expectedDocument,
            $patchedDocument
        );
    }

    /**
     * @test
     * @dataProvider erroringTestsProvider
     *
     * @param $targetDocument
     * @param $patchDocument
     * @param string $error
     * @param string $comment
     */
    public function itFailsOnErroringTests($targetDocument, $patchDocument, $error, $comment = null)
    {
        // Arrange
        if ($comment) {
            $this->setName($comment);
        }

        $exception = null;

        // Act
        $patch = new Patch($targetDocument, $patchDocument);

        try {
            $patch->apply();
        } catch (Exception $thrown) {
            $exception = $thrown;
        }

        // Assert
        $this->assertNotNull($exception, 'An exception should have been thrown');
        $this->assertContains(get_class($exception), [InvalidOperationException::class, FailedTestException::class]);
    }

    /**
     * @param object $test
     *
     * @return array
     */
    public function mapper($test)
    {
        return [
            json_encode($test->doc),
            json_encode($test->patch),
            isset($test->error) ? $test->error : (isset($test->expected) ? json_encode($test->expected) : null),
        ];
    }

    /**
     * @return array
     */
    public function nonErrorTestsProvider()
    {
        return array_map([$this, 'mapper'], array_filter($this->allTestsProvider(), function ($test) {
            return !isset($test->error);
        }));
    }

    /**
     * @return array
     */
    public function erroringTestsProvider()
    {
        return array_map([$this, 'mapper'], array_filter($this->allTestsProvider(), function ($test) {
            return isset($test->error);
        }));
    }

    /**
     * @return array
     */
    public function allTestsProvider()
    {
        $tests = json_decode(file_get_contents('tests/integration/specs.json'))
            + json_decode(file_get_contents('tests/integration/tests.json'));

        return array_filter($tests, function ($test) {
            // If `disabled` is set and set to true, then don't include this test.
            return isset($test->disabled) ? (!$test->disabled) : true;
        });
    }
}
