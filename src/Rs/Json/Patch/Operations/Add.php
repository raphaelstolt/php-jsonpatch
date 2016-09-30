<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Add extends Operation
{
    /**
     * Used for bitmap operations to find out if allowed or not
     *
     * @const int
     */
    const APPLY = 1;

    /**
     * @param \stdClass $operation
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @throws \RuntimeException
     */
    public function __construct(\stdClass $operation)
    {
        $this->assertMandatories($operation);
        parent::__construct('add', $operation);
    }

    /**
     * Guard the mandatory operation property
     *
     * @param  \stdClass $operation The operation structure.
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    protected function assertMandatories(\stdClass $operation)
    {
        if (!property_exists($operation, 'value')) {
            throw new InvalidOperationException('Mandatory value property not set');
        }
    }

    /**
     * Returns the replacement value.
     *
     * @return mixed
     */
    private function getReplacementValue()
    {
        $value = $this->getValue();

        return (is_array($value) || is_object($value)) ? array($value) : $value;
    }

    /**
     * @param  string $targetDocument
     *
     * @return string
     * @throws \Rs\Json\Pointer\InvalidJsonException
     * @throws \Rs\Json\Pointer\InvalidPointerException
     * @throws \Rs\Json\Pointer\NonWalkableJsonException
     */
    public function perform($targetDocument)
    {
        $pointer = new Pointer($targetDocument);
        $rootGet = array();
        $value = $this->getValue();

        try {
            $get = $pointer->get($this->getPath());
        } catch (NonexistentValueReferencedException $e) {
            $get = null;
        }

        $pointerParts = $this->getPointerParts();

        $rootPointer = $pointerParts[0];

        if (count($pointerParts) >= 2) {
            try {
                $rootGet = $pointer->get(Pointer::POINTER_CHAR . implode('/', array_slice($pointerParts, 0, -1)));
            } catch (NonexistentValueReferencedException $e) {
                return $targetDocument;
            }
        }

        $targetDocument = json_decode($targetDocument);

        $lastPointerPart = $pointerParts[count($pointerParts) - 1];
        $replacementValue = $this->getReplacementValue();

        if ($get === null && $lastPointerPart !== Pointer::LAST_ARRAY_ELEMENT_CHAR) {
            if (ctype_digit($lastPointerPart) && $lastPointerPart > count($rootGet)) {
                if ($rootPointer == $lastPointerPart && is_array($targetDocument)) {
                    if ((int) $lastPointerPart <= count($targetDocument) + 1) {
                        array_splice($targetDocument, $lastPointerPart, 0, $replacementValue);
                    }
                }
                return json_encode($targetDocument, JSON_UNESCAPED_UNICODE);
            }
            if (count($pointerParts) === 1) {
                if (is_array($targetDocument)) {
                    $targetDocument[$pointerParts[0]] = $value;
                } else {
                    $targetDocument->{$pointerParts[0]} = $value;
                }
            } elseif (count($pointerParts) > 1) {
                $augmentedDocument = &$targetDocument;
                foreach ($pointerParts as $pointerPart) {
                    if (is_array($augmentedDocument)) {
                        $augmentedDocument = &$augmentedDocument[$pointerPart];
                    } else {
                        $augmentedDocument = &$augmentedDocument->{$pointerPart};
                    }
                }
                $augmentedDocument = $value;
            }
        } else {
            $additionIndex = array_pop($pointerParts);
            $arrayEntryPath = '/' . implode('/', $pointerParts);

            try {
                $targetArray = $pointer->get($arrayEntryPath);
            } catch (NonexistentValueReferencedException $e) {
                $targetArray = null;
            }

            if (is_array($targetArray)) {
                if ($lastPointerPart === Pointer::LAST_ARRAY_ELEMENT_CHAR) {
                    $targetArray[] = $value;
                } else {
                    if (is_numeric($additionIndex)) {
                        array_splice($targetArray, $additionIndex, 0, $replacementValue);
                    } else {
                        $targetArray[$additionIndex] = $value;
                    }
                }
            }

            if (is_object($targetArray)) {
                $targetArray->{$additionIndex} = $value;
            }

            if (is_array($targetArray) || is_object($targetArray)) {
                $augmentedDocument = &$targetDocument;
                foreach ($pointerParts as $pointerPart) {
                    if (is_array($augmentedDocument)) {
                        $augmentedDocument = &$augmentedDocument[$pointerPart];
                    } else {
                        $augmentedDocument = &$augmentedDocument->{$pointerPart};
                    }
                }
                $augmentedDocument = $targetArray;
            }

            if ($targetArray === null) {
                if (is_numeric($additionIndex) && count($targetDocument) > 0) {
                    array_splice($targetDocument, $additionIndex, 0, $replacementValue);
                } else {
                    $targetDocument->{$additionIndex} = $value;
                }
            }
        }

        return json_encode($targetDocument, JSON_UNESCAPED_UNICODE);
    }
}
