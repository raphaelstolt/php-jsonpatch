<?php
namespace Rs\Json;

use Rs\Json\Patch\Document;
use Rs\Json\Patch\Operations\Test;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;
use Rs\Json\Patch\FailedTestException;

class Patch
{
    const MEDIA_TYPE = 'application/json-patch+json';

    /**
     * @var string
     */
    private $targetDocument;

    /**
     * @var \Rs\Json\Patch\Document
     */
    private $jsonPatchDocument;

    /**
     * @param  string $targetDocument
     * @param  string $patchDocument
     * @param  int $allowedPatchOperations
     *
     * @throws \Rs\Json\Patch\InvalidTargetDocumentJsonException
     * @throws \Rs\Json\Patch\InvalidPatchDocumentJsonException
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    public function __construct($targetDocument, $patchDocument, $allowedPatchOperations = null)
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
        $this->jsonPatchDocument = new Document($patchDocument, $allowedPatchOperations);
    }

    /**
     * @return string
     * @throws \Rs\Json\Patch\FailedTestException
     */
    public function apply()
    {
        $patchOperations = $this->jsonPatchDocument->getPatchOperations();
        $patchedDocument = $this->targetDocument;

        $wasObject = '{' === mb_substr(trim($patchedDocument), 0, 1);

        foreach ($patchOperations as $index => $patchOperation) {
            $targetDocument = $patchOperation->perform($patchedDocument);
            if ($patchOperation instanceof Test && $targetDocument === false) {
                $exceptionMessage = 'Failed on Test PatchOperation at index: ' . $index;
                throw new FailedTestException($exceptionMessage);
            }

            if (!$patchOperation instanceof Test) {
                $patchedDocument = $targetDocument;
            }
        }

        $emptyArray = '[]';
        $emptyObject = '{}';
        if ($patchedDocument === $emptyArray && $wasObject) {
            $patchedDocument = $emptyObject;
        }

        if ($patchedDocument === $emptyObject && !$wasObject) {
            $patchedDocument = $emptyArray;
        }

        return $patchedDocument;
    }
}
