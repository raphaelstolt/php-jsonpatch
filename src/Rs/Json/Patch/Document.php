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
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    public function __construct(string $patchDocument, int $allowedPatchOperations = null)
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
     * @param  iterable|string $patchDocument The patch document containing the patch operations.
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @return Operation[]
     */
    private function extractPatchOperations(iterable|string $patchDocument)
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

    private function isEmptyPatchDocument(mixed $patchDocument):bool
    {
        return (empty($patchDocument) || !is_array($patchDocument) || count($patchDocument) === 0);
    }

    /**
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @return \Rs\Json\Patch\Operation| null on unsupported patch operation
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
            case 'copy':
                if (!(($this->allowedPatchOperations & Copy::APPLY) == Copy::APPLY)) {
                    return null;
                }

                return new Copy($possiblePatchOperation);
            case 'move':
                if (!(($this->allowedPatchOperations & Move::APPLY) == Move::APPLY)) {
                    return null;
                }

                return new Move($possiblePatchOperation);
            case 'replace':
                if (!(($this->allowedPatchOperations & Replace::APPLY) == Replace::APPLY)) {
                    return null;
                }

                return new Replace($possiblePatchOperation);
            case 'remove':
                if (!(($this->allowedPatchOperations & Remove::APPLY) == Remove::APPLY)) {
                    return null;
                }

                return new Remove($possiblePatchOperation);
            case 'test':
                if (!(($this->allowedPatchOperations & Test::APPLY) == Test::APPLY)) {
                    return null;
                }

                return new Test($possiblePatchOperation);
            default:
                return null;
        }
    }
}
