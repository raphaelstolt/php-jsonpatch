<?php
namespace Rs\Json;

use Rs\Json\Patch\Document;
use Rs\Json\Patch\Operations\Test;
use Rs\Json\Patch\InvalidJsonException;
use Rs\Json\Patch\FailedTestException;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class Patch
{
    const MEDIA_TYPE = "application/json-patch+json";

    /**
     * @var string
     */
    private $targetDocument;

    /**
     * @var Rs\Json\Patch\Document
     */
    private $patchDocument;

    /**
     * @param string $targetDocument
     * @param string $patchDocument
     * @throws Rs\Json\Patch\InvalidJsonException
     */
    public function __construct($targetDocument, $patchDocument)
    {
      if ($this->lintJson($targetDocument)) {
          $this->targetDocument = $targetDocument;
      }

      if ($this->lintJson($patchDocument)) {
          $this->jsonPatchDocument = new Document($patchDocument);
      }
    }

    /**
     * @return string
     * @throws Rs\Json\Patch\FailedTestException
     */
    public function apply()
    {
        $patchOperations = $this->jsonPatchDocument->getPatchOperations();
        $patchedDocument = $this->targetDocument;
        foreach ($patchOperations as $index => $patchOperation) {
            $targetDocument = $patchOperation->perform($patchedDocument);
            if ($patchOperation instanceof Test && $targetDocument === false) {
                $exceptionMessage = 'Failed on Test PatchOperation at index: ' . $index;
                throw new FailedTestException($exceptionMessage);
            } elseif (!$patchOperation instanceof Test) {
                $patchedDocument = $targetDocument;
            } 
        }
        return $patchedDocument;
    }

    /**
     * @param  mixed $json The Json structure to lint.
     * @return boolean
     * @throws RuntimeException
     * @throws Rs\Json\Patch\InvalidJsonException
     */
    private function lintJson($json)
    {
        if (!class_exists('Seld\\JsonLint\\JsonParser')) {
            throw new \RuntimeException('Unable to lint Json as JsonLint is not installed');
        }

        $parser = new JsonParser;
        $lintResult = $parser->lint($json);

        if ($lintResult instanceof ParsingException) {
            $exceptionMessage = 'Cannot operate on invalid Json. Message: '
                . $lintResult->getMessage();
            throw new InvalidJsonException($exceptionMessage);
        }
        return true;
    }
}
