<?php
namespace Rs\Json;

use Rs\Json\Patch\Document;
use Rs\Json\Patch\Operations\Test;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\FailedTestException;

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
     * @param  string $targetDocument
     * @param  string $patchDocument
     * @throws Rs\Json\Patch\InvalidTargetDocumentJsonException
     * @throws Rs\Json\Patch\InvalidPatchDocumentJsonException
     */
    public function __construct($targetDocument, $patchDocument)
    {
        json_decode($targetDocument, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidTargetDocumentJsonException('Cannot operate on invalid Json.');
        }

        json_decode($patchDocument, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidPatchDocumentJsonException('Cannot operate on invalid Json.');
        }

        $this->targetDocument = $targetDocument;
        $this->jsonPatchDocument = new Document($patchDocument);
    }

    /**
     * @throws Rs\Json\Patch\FailedTestException
     *
     * @return string
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
}
