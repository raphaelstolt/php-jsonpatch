<?php
namespace Rs\Json\Patch;

use Rs\Json\Patch\Operations\Add;
use Rs\Json\Patch\Operations\Copy;
use Rs\Json\Patch\Operations\Move;
use Rs\Json\Patch\Operations\Remove;
use Rs\Json\Patch\Operations\Replace;
use Rs\Json\Patch\Operations\Test;

class Document
{
    /**
     * @var Operation[]
     */
    private $patchOperations;

    /**
     * @var int
     */
    private $allowedPatchOperations;

    /**
     * @param  string $patchDocument
     * @param  int $allowedPatchOperations
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    public function __construct($patchDocument, $allowedPatchOperations = null)
    {
        $defaultPatchOperations = Add::APPLY | Copy::APPLY | Move::APPLY | Remove::APPLY | Replace::APPLY | Test::APPLY;
        $this->allowedPatchOperations = null !== $allowedPatchOperations ? $allowedPatchOperations : $defaultPatchOperations;

        $this->patchOperations = $this->extractPatchOperations($patchDocument);
    }

    /**
     * @return Operation[]
     */
    public function getPatchOperations()
    {
        return $this->patchOperations;
    }

    /**
     * @param  string $patchDocument The patch document containing the patch operations.
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @return Operation[]
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
     *
     * @return bool
     */
    private function isEmptyPatchDocument($patchDocument)
    {
        return (empty($patchDocument) || !is_array($patchDocument) || count($patchDocument) === 0);
    }

    /**
     * @param  \stdClass $possiblePatchOperation
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @return \Rs\Json\Patch\Operation or null on unsupported patch operation
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
                if (!(($this->allowedPatchOperations & Add::APPLY) == Add::APPLY)) {
                    return null;
                }

                return new Add($possiblePatchOperation);
                break;
            case 'copy':
                if (!(($this->allowedPatchOperations & Copy::APPLY) == Copy::APPLY)) {
                    return null;
                }

                return new Copy($possiblePatchOperation);
                break;
            case 'move':
                if (!(($this->allowedPatchOperations & Move::APPLY) == Move::APPLY)) {
                    return null;
                }

                return new Move($possiblePatchOperation);
                break;
            case 'replace':
                if (!(($this->allowedPatchOperations & Replace::APPLY) == Replace::APPLY)) {
                    return null;
                }

                return new Replace($possiblePatchOperation);
                break;
            case 'remove':
                if (!(($this->allowedPatchOperations & Remove::APPLY) == Remove::APPLY)) {
                    return null;
                }

                return new Remove($possiblePatchOperation);
                break;
            case 'test':
                if (!(($this->allowedPatchOperations & Test::APPLY) == Test::APPLY)) {
                    return null;
                }

                return new Test($possiblePatchOperation);
                break;
            default:
                return null;
        }
    }
}
