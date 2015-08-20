<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Add extends Operation
{
    /**
     * @param \stdClass $operation
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
     * @throws Rs\Json\Patch\InvalidOperationException
     */
    protected function assertMandatories(\stdClass $operation)
    {
        if (!property_exists($operation, 'value')) {
            throw new InvalidOperationException('Mandatory value property not set');
        }
    }

    /**
     * @param  string $targetDocument
     *
     * @return string
     */
    public function perform($targetDocument)
    {
        $pointer = new Pointer($targetDocument);

        try {
            $get = $pointer->get($this->getPath());
        } catch (NonexistentValueReferencedException $e) {
            $get = null;
        }

        $pointerParts = $this->getPointerParts();

        $rootPointer = $pointerParts[0];

        if (count($pointerParts) >= 2) {
            try {
               $rootGet = $pointer->get(Pointer::POINTER_CHAR . $rootPointer);
            } catch (NonexistentValueReferencedException $e) {
               return $targetDocument;
            }
        }

        $targetDocument = json_decode($targetDocument, true);

        $lastPointerPart = $pointerParts[count($pointerParts) - 1];

        if ($get === null && $lastPointerPart !== Pointer::LAST_ARRAY_ELEMENT_CHAR) {
            if (ctype_digit($lastPointerPart) && $lastPointerPart > count($rootGet)) {
                return json_encode($targetDocument);
            }
            if (count($pointerParts) === 1) {
                $targetDocument[$pointerParts[0]] = $this->getValue();
            } elseif (count($pointerParts) > 1) {
                $augmentedDocument = &$targetDocument;
                foreach ($pointerParts as $pointerPart) {
                    $augmentedDocument = &$augmentedDocument[$pointerPart];
                }
                $augmentedDocument = $this->getValue();
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
                    $targetArray[] = $this->getValue();
                } else {
                    if (is_numeric($additionIndex)) {
                        $replacement = is_array($this->getValue()) || is_object($this->getValue()) ? array($this->getValue()) : $this->getValue();
                        array_splice($targetArray, $additionIndex, 0, $replacement);
                    } else {
                        $targetArray[$additionIndex] = $this->getValue();
                    }
                }
                $augmentedDocument = &$targetDocument;
                foreach ($pointerParts as $pointerPart) {
                    $augmentedDocument = &$augmentedDocument[$pointerPart];
                }
                $augmentedDocument = $targetArray;
            }

            if ($targetArray === null) {
                $targetDocument[$additionIndex] = $this->getValue();
            }
        }

        return json_encode($targetDocument);
    }
}
