<?php
namespace Rs\Json\Patch;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Patch\Operations\Add;
use Rs\Json\Patch\Operations\Copy;
use Rs\Json\Patch\Operations\Move;
use Rs\Json\Patch\Operations\Remove;
use Rs\Json\Patch\Operations\Replace;
use Rs\Json\Patch\Operations\Test;

class Document
{
    /**
     * @var array
     */
    private $patchOperations;

    /**
     * @param  string $patchDocument
     * @throws Rs\Json\Patch\InvalidOperationException
     */
    public function __construct($patchDocument)
    {
        $this->patchOperations = $this->extractPatchOperations($patchDocument);
    }

    /**
     * @return array
     */
    public function getPatchOperations()
    {
        return $this->patchOperations;
    }

    /**
     * @param  string $patchDocument The patch document containing the patch operations.
     * @throws Rs\Json\Patch\InvalidOperationException
     *
     * @return array
     */
    private function extractPatchOperations($patchDocument)
    {
        $patchDocument = json_decode($patchDocument);

        if ($this->isEmptyPatchDocument($patchDocument)) {
            $exceptionMessage = sprintf(
                "Unable to extract patch operations from '%s'",
                json_encode($patchDocument)
            );
            throw new InvalidOperationException($exceptionMessage);
        }

        $patchOperations = array();

        foreach ($patchDocument as $index => $possiblePatchOperation) {
            $operation = $this->patchOperationFactory($possiblePatchOperation);
            if ($operation instanceof Operation) {
                $patchOperations[] = $operation;
            }
        }
        return $patchOperations;
    }

    /**
     * @param mixed $patchDocument
     */
    private function isEmptyPatchDocument($patchDocument)
    {
        if (empty($patchDocument) || !is_array($patchDocument) || count($patchDocument) === 0) {
            return true;
        }
        return false;
    }

    /**
     * @param  \stdClass $possiblePatchOperation
     * @throws Rs\Json\Patch\InvalidOperationException
     *
     * @return Rs\Json\Patch\Operation or null on unsupported patch operation
     */
    private function patchOperationFactory(\stdClass $possiblePatchOperation)
    {
        if (!isset($possiblePatchOperation->op)) {
            $exceptionMessage = sprintf(
                "No operation set for patch operation '%s'",
                json_encode($possiblePatchOperation)
            );
            throw new InvalidOperationException($exceptionMessage);
        }

        switch ($possiblePatchOperation->op) {
            case 'add':
                return new Add($possiblePatchOperation);
                break;
            case 'copy':
                return new Copy($possiblePatchOperation);
                break;
            case 'move':
                return new Move($possiblePatchOperation);
                break;
            case 'replace':
                return new Replace($possiblePatchOperation);
                break;
            case 'remove':
                return new Remove($possiblePatchOperation);
                break;
            case 'test':
                return new Test($possiblePatchOperation);
                break;
            default:
                return null;
        }
    }
}
